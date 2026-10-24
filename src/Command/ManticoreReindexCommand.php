<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\Product;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Logging\Middleware;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Attribute\Target;

#[AsCommand(name: 'app:manticore:reindex', description: 'Первоначальная заливка данных в RT индекс Manticore')]
class ManticoreReindexCommand extends Command
{
    private EntityManagerInterface $entityManager;
    private Connection $manticoreConnection;

    public function __construct(
        EntityManagerInterface $entityManager,
        #[Target('manticoreConnection')]
        Connection $manticoreConnection
    )
    {
        parent::__construct();

        $this->entityManager = $entityManager;
        $this->manticoreConnection = $manticoreConnection;
    }

    /**
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Запуск полной переиндексации товаров в Manticore Search');

        // 1. Современное отключение SQL-логов в Doctrine 3/4 для предотвращения утечки памяти
        $nullLoggerMiddleware = new Middleware(new NullLogger());
        $this->entityManager->getConnection()->getConfiguration()->setMiddlewares([$nullLoggerMiddleware]);
        $this->manticoreConnection->getConfiguration()->setMiddlewares([$nullLoggerMiddleware]);

        // 2. Считаем общее количество записей для ProgressBar (быстрый COUNT без гидратации объектов)
        $io->text('Подсчет общего количества товаров...');
        $totalCount = (int) $this->entityManager
            ->createQuery('SELECT COUNT(p.id) FROM App\Entity\Product p WHERE p.status = true')
            ->getSingleScalarResult();

        if ($totalCount === 0) {
            $io->warning('В базе данных не найдено товаров для индексации.');

            return Command::SUCCESS;
        }

        $this->manticoreConnection->executeStatement("
            CREATE TABLE IF NOT EXISTS products (
                external_id bigint,
                title text,
                description text,
                model text,
                article text,
                status int
            ) morphology='stem_enru' html_strip='1' min_word_len='3' index_exact_words='1'
        ");

        // 3. Очищаем индекс Manticore перед заливкой
        $io->text('Очистка таблицы Manticore products...');
        $this->manticoreConnection->executeStatement('TRUNCATE TABLE products');

        // 4. Инициализация ProgressBar
        $progressBar = new ProgressBar($output, $totalCount);
        $progressBar->setFormat(' %current%/%max% [%bar%] %percent%% -- Оставшееся время: %remaining% (Скорость: %estimated%)');
        $progressBar->start();

        // 5. Потоковое чтение через курсор
        $query = $this->entityManager->createQuery('SELECT p FROM App\Entity\Product p WHERE p.status = true');
        $iterableResult = $query->toIterable();

        $batchSize = 1000;
        $batchData = [];

        foreach ($iterableResult as $product) {
            /** @var Product $product */

            $batchData[] = [
                $product->getId(),
                $product->getId(), // external_id
                (string) $product->getTitle(),
                (string) $product->getDescription(),
                (string) $product->getModel(),
                (string) $product->getArticle(),
                (int) $product->isStatus(),
            ];

            if (count($batchData) >= $batchSize) {
                $this->insertBatch($batchData);

                // Двигаем прогресс-бар на размер пачки
                $progressBar->advance(count($batchData));

                // Очищаем память Doctrine и локальный массив
                $this->entityManager->clear();
                $batchData = [];
            }
        }

        // Записываем оставшиеся строки
        if (count($batchData) > 0) {
            $this->insertBatch($batchData);
            $progressBar->advance(count($batchData));
            $this->entityManager->clear();
        }

        $progressBar->finish();
        $io->newLine(2);
        $io->success(sprintf('Успешно проиндексировано товаров: %d', $totalCount));

        return Command::SUCCESS;
    }

    /**
     * Сборка пакетного запроса REPLACE INTO со строгими типами
     */
    private function insertBatch(array $batch): void
    {
        if (empty($batch)) {
            return;
        }

        // Количество полей в одной строке (id, external_id, title, description, model, article, status)
        $fieldsCount = 7;

        // Генерируем плейсхолдеры (?,?,?,?,?,?,?) для каждой строки
        $rowPlaceholder = '(' . implode(',', array_fill(0, $fieldsCount, '?')) . ')';
        $allPlaceholders = implode(',', array_fill(0, count($batch), $rowPlaceholder));

        $sql = "REPLACE INTO products (id, external_id, title, description, model, article, status) VALUES " . $allPlaceholders;

        // Линеаризация (разворачивание) двумерного массива в плоский список параметров
        $flattenedParams = [];
        foreach ($batch as $row) {
            foreach ($row as $value) {
                $flattenedParams[] = $value;
            }
        }

        $this->manticoreConnection->executeStatement($sql, $flattenedParams);
    }
}

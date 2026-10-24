<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\Product;
use App\Entity\ProductImage;
use App\Service\ProductImagePathNormalizer;
use Doctrine\ORM\EntityManagerInterface;
use Override;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:fix-product-images',
    description: 'Добавляет /uploads в начало путей картинок продуктов, если его нет'
)]
final class FixProductImagesCommand extends Command
{
    private const int BATCH_SIZE = 200;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly ProductImagePathNormalizer $normalizer,
    ) {
        parent::__construct();
    }

    #[Override]
    protected function configure(): void
    {
        $this->addOption(
            'dry-run',
            null,
            InputOption::VALUE_NONE,
            'Показать изменения, но не сохранять их'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $dryRun = (bool) $input->getOption('dry-run');

        $updated = $this->fixProductImages($dryRun, $output, Product::class, 'Product', 'getImage', 'setImage');
        $updated += $this->fixProductImages($dryRun, $output, ProductImage::class, 'ProductImage', 'getImage', 'setImage');

        $output->writeln(sprintf(
            '%s: %d',
            $dryRun ? 'Было бы обновлено' : 'Обновлено',
            $updated,
        ));

        return Command::SUCCESS;
    }

    /**
     * @template T of object
     *
     * @param class-string<T> $class
     */
    private function fixProductImages(
        bool $dryRun,
        OutputInterface $output,
        string $class,
        string $label,
        string $getter,
        string $setter,
    ): int {
        $updated = 0;
        $offset  = 0;

        while (true) {
            $entities = $this->entityManager
                ->createQueryBuilder()
                ->select('e')
                ->from($class, 'e')
                ->orderBy('e.id', 'ASC')
                ->setMaxResults(self::BATCH_SIZE)
                ->setFirstResult($offset)
                ->getQuery()
                ->getResult();

            if ($entities === []) {
                break;
            }

            foreach ($entities as $entity) {
                $oldPath = $entity->{$getter}();
                $newPath = $this->normalizer->normalize($oldPath);

                if ($oldPath !== $newPath) {
                    $output->writeln(sprintf(
                        '%s #%d: %s -> %s',
                        $label,
                        $entity->getId(),
                        var_export($oldPath, true),
                        var_export($newPath, true),
                    ));

                    if (!$dryRun) {
                        $entity->{$setter}($newPath);
                        ++$updated;
                    }
                }
            }

            if (!$dryRun) {
                $this->entityManager->flush();
                $this->entityManager->clear();
            }

            $offset += self::BATCH_SIZE;
        }

        return $updated;
    }
}

<?php

declare(strict_types=1);

namespace App\Tests\Controller\Admin;

use App\Entity\Category;
use Doctrine\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Тесты списка категорий: фильтрация, сортировка, постраничная навигация
 * и устойчивость к пустым/некорректным query-параметрам.
 *
 * Каждый тест обёрнут в транзакцию (DAMA DoctrineTestBundle),
 * поэтому созданные данные откатываются автоматически.
 */
final class CategoriesControllerTest extends WebTestCase
{
    private const INDEX_URL = '/admin/categories/';

    private KernelBrowser $client;
    private ObjectManager $manager;

    private Category $electronics;  // главная, активна
    private Category $clothes;      // главная, отключена
    private Category $phones;       // дочерняя от «Электроника»
    private Category $smartphones;  // дочерняя от «Телефоны»

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->manager = static::getContainer()->get('doctrine')->getManager();

        $this->electronics = $this->createCategory('Электроника');
        $this->clothes = $this->createCategory('Одежда', status: false);
        $this->phones = $this->createCategory('Телефоны', $this->electronics);
        $this->smartphones = $this->createCategory('Смартфоны', $this->phones);
        $this->manager->flush();
    }

    public function testIndexShowsAllCategories(): void
    {
        $crawler = $this->client->request('GET', self::INDEX_URL);

        self::assertResponseIsSuccessful();
        self::assertCount(4, $crawler->filter('table tbody tr'));

        $tableText = $crawler->filter('table')->text();
        self::assertStringContainsString('Электроника', $tableText);
        self::assertStringContainsString('Одежда', $tableText);
        self::assertStringContainsString('Телефоны', $tableText);
        self::assertStringContainsString('Смартфоны', $tableText);
    }

    public function testFilterById(): void
    {
        $crawler = $this->client->request('GET', self::INDEX_URL, [
            'id' => (string) $this->phones->getId(),
        ]);

        self::assertResponseIsSuccessful();
        self::assertSame(['Телефоны'], $this->columnValues($crawler, 3));
    }

    public function testFilterByTitle(): void
    {
        $crawler = $this->client->request('GET', self::INDEX_URL, [
            'title' => 'Тел',
        ]);

        self::assertResponseIsSuccessful();
        self::assertSame(['Телефоны'], $this->columnValues($crawler, 3));
    }

    public function testFilterByTitleWithoutResults(): void
    {
        $this->client->request('GET', self::INDEX_URL, [
            'title' => 'Нет такой категории',
        ]);

        self::assertResponseIsSuccessful();
        self::assertStringContainsString('Категории не найдены', (string) $this->client->getResponse()->getContent());
    }

    public function testFilterByParent(): void
    {
        $crawler = $this->client->request('GET', self::INDEX_URL, [
            'parent' => (string) $this->electronics->getId(),
        ]);

        self::assertResponseIsSuccessful();
        self::assertSame(['Телефоны'], $this->columnValues($crawler, 3));
    }

    public function testFilterOnlyMain(): void
    {
        $crawler = $this->client->request('GET', self::INDEX_URL, [
            'onlyMain' => '1',
        ]);

        self::assertResponseIsSuccessful();

        $titles = $this->columnValues($crawler, 3);
        sort($titles);
        self::assertSame(['Одежда', 'Электроника'], $titles);
    }

    public function testFilterByStatus(): void
    {
        $crawler = $this->client->request('GET', self::INDEX_URL, [
            'status' => '0',
        ]);
        self::assertSame(['Одежда'], $this->columnValues($crawler, 3));

        $crawler = $this->client->request('GET', self::INDEX_URL, [
            'status' => '1',
        ]);
        self::assertCount(3, $crawler->filter('table tbody tr'));
    }

    public function testSortByNameAsc(): void
    {
        $crawler = $this->client->request('GET', self::INDEX_URL, [
            'sort' => 'name',
            'dir' => 'asc',
        ]);

        self::assertResponseIsSuccessful();
        self::assertSame(
            ['Одежда', 'Смартфоны', 'Телефоны', 'Электроника'],
            $this->columnValues($crawler, 3),
        );
    }

    public function testSortByNameDesc(): void
    {
        $crawler = $this->client->request('GET', self::INDEX_URL, [
            'sort' => 'name',
            'dir' => 'desc',
        ]);

        self::assertSame(
            ['Электроника', 'Телефоны', 'Смартфоны', 'Одежда'],
            $this->columnValues($crawler, 3),
        );
    }

    public function testSortByIdAsc(): void
    {
        $crawler = $this->client->request('GET', self::INDEX_URL, [
            'sort' => 'id',
            'dir' => 'asc',
        ]);

        self::assertSame(
            [
                (string) $this->electronics->getId(),
                (string) $this->clothes->getId(),
                (string) $this->phones->getId(),
                (string) $this->smartphones->getId(),
            ],
            $this->columnValues($crawler, 2),
        );
    }

    public function testSortByParent(): void
    {
        // По названию родителя: NULL-значения MySQL ставит первыми при ASC.
        $crawler = $this->client->request('GET', self::INDEX_URL, [
            'sort' => 'parent',
            'dir' => 'asc',
        ]);
        $ids = $this->columnValues($crawler, 2);
        self::assertSame((string) $this->smartphones->getId(), $ids[2]);
        self::assertSame((string) $this->phones->getId(), $ids[3]);

        $crawler = $this->client->request('GET', self::INDEX_URL, [
            'sort' => 'parent',
            'dir' => 'desc',
        ]);
        $ids = $this->columnValues($crawler, 2);
        self::assertSame((string) $this->phones->getId(), $ids[0]);
        self::assertSame((string) $this->smartphones->getId(), $ids[1]);
    }

    public function testSortByStatus(): void
    {
        // ASC: сначала отключенные (status = 0) — только «Одежда».
        $crawler = $this->client->request('GET', self::INDEX_URL, [
            'sort' => 'status',
            'dir' => 'asc',
        ]);
        self::assertSame((string) $this->clothes->getId(), $this->columnValues($crawler, 2)[0]);

        // DESC: активные первыми, «Одежда» в конце.
        $crawler = $this->client->request('GET', self::INDEX_URL, [
            'sort' => 'status',
            'dir' => 'desc',
        ]);
        $ids = $this->columnValues($crawler, 2);
        self::assertSame((string) $this->clothes->getId(), end($ids));
    }

    public function testDefaultSortingIsIdDesc(): void
    {
        $crawler = $this->client->request('GET', self::INDEX_URL);

        self::assertSame(
            [
                (string) $this->smartphones->getId(),
                (string) $this->phones->getId(),
                (string) $this->clothes->getId(),
                (string) $this->electronics->getId(),
            ],
            $this->columnValues($crawler, 2),
        );
    }

    public function testEmptyQueryParametersDoNotBreakThePage(): void
    {
        // Регрессия: пустые значения параметров не должны приводить к исключению.
        $crawler = $this->client->request('GET', self::INDEX_URL, [
            'id' => '',
            'title' => '',
            'parent' => '',
            'onlyMain' => '',
            'status' => '',
            'sort' => '',
            'dir' => '',
        ]);

        self::assertResponseIsSuccessful();
        self::assertCount(4, $crawler->filter('table tbody tr'));
    }

    public function testInvalidQueryParametersFallBackToDefaults(): void
    {
        $crawler = $this->client->request('GET', self::INDEX_URL, [
            'id' => 'abc',
            'parent' => '-5',
            'status' => 'не статус',
            'sort' => 'hack',
            'dir' => 'sideways',
        ]);

        self::assertResponseIsSuccessful();

        // Фильтры проигнорированы, сортировка по умолчанию — id DESC.
        self::assertCount(4, $crawler->filter('table tbody tr'));
        self::assertSame((string) $this->smartphones->getId(), $this->columnValues($crawler, 2)[0]);
    }

    public function testPagination(): void
    {
        // Ещё 21 категория: 25 штук = 2 страницы по 20.
        for ($i = 1; $i <= 21; ++$i) {
            $this->createCategory(\sprintf('Каталог %d', $i));
        }
        $this->manager->flush();

        $crawler = $this->client->request('GET', self::INDEX_URL);
        self::assertCount(20, $crawler->filter('table tbody tr'));

        $crawler = $this->client->request('GET', '/admin/categories/page/2');
        self::assertResponseIsSuccessful();
        self::assertCount(5, $crawler->filter('table tbody tr'));
    }

    public function testPaginationKeepsFilterAndSorting(): void
    {
        for ($i = 1; $i <= 21; ++$i) {
            $this->createCategory(\sprintf('Каталог %d', $i));
        }
        $this->manager->flush();

        $crawler = $this->client->request('GET', '/admin/categories/page/2', [
            'sort' => 'name',
            'dir' => 'asc',
        ]);

        self::assertResponseIsSuccessful();
        self::assertCount(5, $crawler->filter('table tbody tr'));

        // Ссылки пагинации должны сохранять текущую сортировку.
        $pagination = $crawler->filter('nav[aria-label="Постраничная навигация"]');
        self::assertStringContainsString('sort=name', $pagination->html());
        self::assertStringContainsString('dir=asc', $pagination->html());
    }

    public function testPageOutOfRangeReturnsNotFound(): void
    {
        $this->client->request('GET', '/admin/categories/page/99');

        self::assertResponseStatusCodeSame(404);
    }

    private function createCategory(string $title, ?Category $parent = null, bool $status = true): Category
    {
        $category = new Category();
        $category->setTitle($title);
        $category->setParent($parent);
        $category->setStatus($status);

        $this->manager->persist($category);

        return $category;
    }

    /**
     * Значения колонки таблицы (1 = «#», 2 = ID, 3 = Название, 4 = Родитель, 5 = Статус).
     *
     * @return list<string>
     */
    private function columnValues(Crawler $crawler, int $column): array
    {
        return $crawler
            ->filter(\sprintf('table tbody tr td:nth-child(%d)', $column))
            ->each(static fn (Crawler $td): string => trim($td->text()));
    }
}

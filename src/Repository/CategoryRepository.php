<?php

declare(strict_types=1);

namespace App\Repository;

use App\Dto\Admin\CategoryFilter;
use App\Entity\Category;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Cache\InvalidArgumentException;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

/**
 * @extends ServiceEntityRepository<Category>
 */
class CategoryRepository extends ServiceEntityRepository
{
    /**
     * Поля, по которым разрешена сортировка.
     */
    private const array SORT_COLUMNS = [
        'id' => 'c.id',
        'name' => 'c.title',
        'parent' => 'p.title',
        'status' => 'c.status',
    ];

    private TagAwareCacheInterface $cache;

    public function __construct(ManagerRegistry $registry, TagAwareCacheInterface $tagAwareRedisPool)
    {
        parent::__construct($registry, Category::class);

        $this->cache = $tagAwareRedisPool;
    }

    /**
     * @param int $page
     * @param int $limit
     * @param CategoryFilter $filter
     *
     * @return array
     */
    public function findPaginatedWithFilter(
        CategoryFilter $filter,
        int $page = 1,
        int $limit = 10
    ): array {
        // 'c' - текущая категория, 'p' - её родитель
        $queryBuilder = $this->createQueryBuilder('c')
            ->select(
                'c.id',
                'c.title',
                'c.created_at',
                'c.updated_at',
                'c.status',
                'p.id AS parent_id',
                'p.title AS parent_name'
            )
            ->leftJoin('c.parent', 'p');

        if ($filter->id !== null) {
            $queryBuilder->andWhere('c.id = :id')
                ->setParameter('id', $filter->id);
        }

        if ($filter->title !== null && $filter->title !== '') {
            $queryBuilder->andWhere('c.title LIKE :search')
                ->setParameter('search', '%' . $filter->title . '%');
        }

        if ($filter->parent !== null) {
            $queryBuilder->andWhere('p.id = :parent')
                ->setParameter('parent', $filter->parent);
        }

        if ($filter->onlyMain) {
            // Главные категории - корневые, без родителя
            $queryBuilder->andWhere('c.parent IS NULL');
        }

        if ($filter->status !== null && $filter->status !== '') {
            $queryBuilder->andWhere('c.status = :status')
                ->setParameter('status', (bool) $filter->status);
        }

        $countQueryBuilder = clone $queryBuilder;

        $sortColumn = self::SORT_COLUMNS[$filter->sortBy] ?? self::SORT_COLUMNS['id'];
        $direction = $filter->sortDir === 'asc' ? 'ASC' : 'DESC';
        $queryBuilder->orderBy($sortColumn, $direction);

        if ($sortColumn !== self::SORT_COLUMNS['id']) {
            $queryBuilder->addOrderBy('c.id', 'DESC');
        }

        $offset = ($page - 1) * $limit;
        $queryBuilder->setFirstResult($offset)->setMaxResults($limit);

        // getScalarResult() делает массив полностью плоским (без индексов '0')
        $items = $queryBuilder->getQuery()->getScalarResult();

        $countItems = $countQueryBuilder->select('COUNT(c.id)')
            ->setFirstResult(null)
            ->setMaxResults(null)
            ->getQuery()
            ->getSingleScalarResult();

        return [
            'items' => $items,
            'total' => (int) $countItems,
        ];
    }

    /**
     * Список категорий для фильтра по родителю.
     *
     * @return array<int, string, int|null, array>
     *
     * @throws InvalidArgumentException
     */
    public function findChoicesForFilter(): array
    {
        $cacheKey = 'categoriesChoicesForFilter';

        return $this->cache->get($cacheKey, function (ItemInterface $item) {
            $item->tag(['categoriesTreeList']);

            $rows = $this->createQueryBuilder('c')
                ->select('c.id', 'c.title', 'IDENTITY(c.parent) AS parent_id')
                ->orderBy('c.title', 'ASC')
                ->getQuery()
                ->getScalarResult();

            $categories = [];
            foreach ($rows as $row) {
                $categories[$row['id']] = [
                    'id' => (int) $row['id'],
                    'title' => (string) $row['title'],
                    'parent_id' => $row['parent_id'] ? (int) $row['parent_id'] : null,
                    'children' => []
                ];
            }

            $tree = [];
            foreach ($categories as &$category) {
                if ($category['parent_id'] === null) {
                    $tree[] = &$category;
                } else {
                    if (isset($categories[$category['parent_id']])) {
                        $categories[$category['parent_id']]['children'][] = &$category;
                    } else {
                        $tree[] = &$category;
                    }
                }
            }

            $flatChoices = [];
            $flatten = function (array $nodes, int $depth = 0) use (&$flatten, &$flatChoices) {
                foreach ($nodes as $node) {
                    $flatChoices[] = [
                        'id' => $node['id'],
                        'title' => $node['title'],
                        'depth' => $depth
                    ];

                    if (!empty($node['children'])) {
                        $flatten($node['children'], $depth + 1);
                    }
                }
            };

            $flatten($tree);

            return $flatChoices;
        });
    }
}

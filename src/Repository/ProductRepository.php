<?php

declare(strict_types=1);

namespace App\Repository;

use App\Dto\Admin\ProductFilter;
use App\Entity\Product;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

/**
 * @extends ServiceEntityRepository<Product>
 */
class ProductRepository extends ServiceEntityRepository
{
    /**
     * Поля, по которым разрешена сортировка товаров.
     */
    private const array SORT_COLUMNS = [
        'id' => 'p.id',
        'title' => 'p.title',
        'category' => 'c.title',
        'article' => 'p.article',
        'status' => 'p.status',
        'created_at' => 'p.created_at',
    ];

    private TagAwareCacheInterface $cache;

    public function __construct(ManagerRegistry $registry, TagAwareCacheInterface $tagAwareRedisPool)
    {
        parent::__construct($registry, Product::class);

        $this->cache = $tagAwareRedisPool;
    }

    /**
     * Список товаров с фильтрацией, сортировкой и постраничной навигацией.
     */
    public function findPaginatedWithFilter(
        ProductFilter $filter,
        int $page = 1,
        int $limit = 10
    ): array {
        // 'p' — товар, 'c' — его основная категория
        $queryBuilder = $this->createQueryBuilder('p')
            ->select(
                'p.id',
                'p.title',
                'p.article',
                'p.price',
                'p.status',
                'p.created_at',
                'p.updated_at',
                'c.id AS category_id',
                'c.title AS category_title'
            )
            ->leftJoin('p.main_category', 'c');

        if ($filter->id !== null) {
            $queryBuilder->andWhere('p.id = :id')
                ->setParameter('id', $filter->id);
        }

        if ($filter->title !== null && $filter->title !== '') {
            $queryBuilder->andWhere('p.title LIKE :search')
                ->setParameter('search', '%' . $filter->title . '%');
        }

        if ($filter->category !== null) {
            $queryBuilder->andWhere('c.id = :category')
                ->setParameter('category', $filter->category);
        }

        if ($filter->article !== null && $filter->article !== '') {
            $queryBuilder->andWhere('p.article LIKE :article')
                ->setParameter('article', '%' . $filter->article . '%');
        }

        if ($filter->status !== null) {
            $queryBuilder->andWhere('p.status = :status')
                ->setParameter('status', $filter->status);
        }

        $countQueryBuilder = clone $queryBuilder;

        $sortColumn = self::SORT_COLUMNS[$filter->sortBy] ?? self::SORT_COLUMNS['id'];
        $direction = $filter->sortDir === 'asc' ? 'ASC' : 'DESC';
        $queryBuilder->orderBy($sortColumn, $direction);

        if ($sortColumn !== self::SORT_COLUMNS['id']) {
            $queryBuilder->addOrderBy('p.id', 'DESC');
        }

        $offset = ($page - 1) * $limit;
        $queryBuilder->setFirstResult($offset)->setMaxResults($limit);

        // getScalarResult() возвращает плоский массив (без вложенных индексов)
        $items = $queryBuilder->getQuery()->getScalarResult();

        $countItems = $countQueryBuilder->select('COUNT(p.id)')
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
     * Список товаров для select2 (id => «Название — Артикул»).
     *
     * @return array<int, string>
     */
    public function findChoicesForFilter(): array
    {
        $rows = $this->createQueryBuilder('p')
            ->select('p.id', 'p.title', 'p.article')
            ->andWhere('p.status = true')
            ->orderBy('p.title', 'ASC')
            ->getQuery()
            ->getScalarResult();

        $choices = [];
        foreach ($rows as $row) {
            $choices[(int) $row['id']] = $row['title'] . ' — ' . $row['article'];
        }

        return $choices;
    }
}

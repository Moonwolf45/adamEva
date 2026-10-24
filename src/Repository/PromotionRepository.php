<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Promotion;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Cache\InvalidArgumentException;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

class PromotionRepository extends ServiceEntityRepository
{
    public const string TAG_ACTIVE = 'promotionsActive';

    private TagAwareCacheInterface $cache;

    public function __construct(
        ManagerRegistry $registry,
        TagAwareCacheInterface $tagAwareRedisPool
    ) {
        parent::__construct($registry, Promotion::class);

        $this->cache = $tagAwareRedisPool;
    }

    /**
     * Кешируемый снимок всех активных акций для витрины:
     * [
     *   'promotions' => [id => ['id' =>, 'title' =>, 'type' =>, 'value' =>]],
     *   'product_map' => [productId => [promotionId, ...]],
     *   'category_map' => [categoryId => [promotionId, ...]],
     * ]
     *
     * @throws InvalidArgumentException
     */
    public function findActiveSnapshot(): array
    {
        return $this->cache->get('promotionsActiveSnapshot', function (ItemInterface $item): array {
            $item->tag([self::TAG_ACTIVE]);
            $item->expiresAfter(120);

            $now = new \DateTimeImmutable();

            $promotions = $this->createQueryBuilder('p')
                ->select('p.id', 'p.title', 'p.type', 'p.value')
                ->andWhere('p.status = :status')
                ->andWhere('p.starts_at IS NULL OR p.starts_at <= :now')
                ->andWhere('p.ends_at IS NULL OR p.ends_at >= :now')
                ->setParameter('status', true)
                ->setParameter('now', $now)
                ->getQuery()
                ->getScalarResult();

            $categoryLinks = $this->createQueryBuilder('p')
                ->select('p.id AS promotion_id', 'IDENTITY(c) AS category_id')
                ->innerJoin('p.categories', 'c')
                ->andWhere('p.status = :status')
                ->andWhere('p.starts_at IS NULL OR p.starts_at <= :now')
                ->andWhere('p.ends_at IS NULL OR p.ends_at >= :now')
                ->setParameter('status', true)
                ->setParameter('now', $now)
                ->getQuery()
                ->getScalarResult();

            $productLinks = $this->createQueryBuilder('p')
                ->select('p.id AS promotion_id', 'IDENTITY(pr) AS product_id')
                ->innerJoin('p.products', 'pr')
                ->andWhere('p.status = :status')
                ->andWhere('p.starts_at IS NULL OR p.starts_at <= :now')
                ->andWhere('p.ends_at IS NULL OR p.ends_at >= :now')
                ->setParameter('status', true)
                ->setParameter('now', $now)
                ->getQuery()
                ->getScalarResult();

            $snapshot = [
                'promotions' => [],
                'product_map' => [],
                'category_map' => [],
            ];

            foreach ($promotions as $row) {
                $snapshot['promotions'][(int) $row['id']] = [
                    'id' => (int) $row['id'],
                    'title' => (string) $row['title'],
                    'type' => (string) $row['type'],
                    'value' => (int) $row['value'],
                ];
            }

            foreach ($categoryLinks as $row) {
                $snapshot['category_map'][(int) $row['category_id']][] = (int) $row['promotion_id'];
            }

            foreach ($productLinks as $row) {
                $snapshot['product_map'][(int) $row['product_id']][] = (int) $row['promotion_id'];
            }

            return $snapshot;
        });
    }

    /**
     * Активные акции в данный момент времени.
     */
    public function findActive(): array
    {
        $now = new \DateTimeImmutable();

        return $this->createQueryBuilder('p')
            ->andWhere('p.status = :status')
            ->andWhere('p.starts_at IS NULL OR p.starts_at <= :now')
            ->andWhere('p.ends_at IS NULL OR p.ends_at >= :now')
            ->setParameter('status', true)
            ->setParameter('now', $now)
            ->orderBy('p.id', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Активные акции, где задан список категорий/товаров (для витрины).
     */
    public function findActiveWithTargets(): array
    {
        return array_filter(
            $this->findActive(),
            static fn (Promotion $p): bool => $p->getCategories()->count() > 0 || $p->getProducts()->count() > 0,
        );
    }
}

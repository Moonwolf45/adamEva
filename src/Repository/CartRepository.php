<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Cart;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class CartRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Cart::class);
    }

    public function findOneByUuid(string $uuid): ?Cart
    {
        return $this->createQueryBuilder('c')
            ->leftJoin('c.items', 'i')
            ->where('c.uuid = :uuid')
            ->setParameter('uuid', $uuid)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Удаляет корзины (вместе с позициями через DB-cascade),
     * не обновлявшиеся раньше указанной даты.
     */
    public function deleteStale(\DateTimeImmutable $boundary): int
    {
        return (int) $this->createQueryBuilder('c')
            ->delete()
            ->where('c.updatedAt < :boundary')
            ->setParameter('boundary', $boundary)
            ->getQuery()
            ->execute();
    }
}

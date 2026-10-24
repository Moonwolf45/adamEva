<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\ResetPasswordRequest;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use SymfonyCasts\Bundle\ResetPassword\Model\ResetPasswordRequestInterface;
use SymfonyCasts\Bundle\ResetPassword\Persistence\Repository\ResetPasswordRequestRepositoryTrait;
use SymfonyCasts\Bundle\ResetPassword\Persistence\ResetPasswordRequestRepositoryInterface;

/**
 * @extends ServiceEntityRepository<ResetPasswordRequest>
 */
class ResetPasswordRequestRepository extends ServiceEntityRepository implements ResetPasswordRequestRepositoryInterface
{
    use ResetPasswordRequestRepositoryTrait;

    private ManagerRegistry $registry;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ResetPasswordRequest::class);

        $this->registry = $registry;
    }

    public function createResetPasswordRequest(
        object $user,
        \DateTimeInterface $expiresAt,
        string $selector,
        string $hashedToken
    ): ResetPasswordRequestInterface
    {
        return new ResetPasswordRequest($user, $expiresAt, $selector, $hashedToken);
    }

    public function findResetPasswordRequest(string $selector): ?ResetPasswordRequestInterface
    {
        /** @var ResetPasswordRequest|null $request */
        $request = $this->findOneBy(['selector' => $selector]);

        if ($request) {
            $em = $this->registry->getManagerForClass($request->getUserType());
            $userCustomerRepository = $em->getRepository($request->getUserType());
            $userCustomer = $userCustomerRepository->find($request->getUserId());

            if ($userCustomer) {
                $request->setUserObject($userCustomer);
            }
        }

        return $request;
    }

    /**
     * Адаптированный метод из трейта пакета под ваши поля user_type и user_id
     */
    public function getMostRecentNonExpiredRequestDate(object $user): ?\DateTimeInterface
    {
        $qb = $this->createQueryBuilder('r')
            ->select('r.requestedAt') // Doctrine вернет массив, а не объект сущности
            ->where('r.user_type = :user_type')
            ->andWhere('r.user_id = :user_id')
            ->orderBy('r.requestedAt', 'DESC')
            ->setMaxResults(1)
            ->setParameter('user_type', get_class($user))
            ->setParameter('user_id', (string) $user->getId())
        ;

        // Получаем массив результатов
        $result = $qb->getQuery()->getOneOrNullResult();

        // Если ничего не найдено, или в массиве нет нужного ключа
        if (null === $result || !isset($result['requestedAt'])) {
            return null;
        }

        // Возвращаем объект даты напрямую из массива
        return $result['requestedAt'];
    }

    /**
     * Адаптированный метод удаления запросов из трейта пакета
     */
    public function removeResetPasswordRequest(ResetPasswordRequestInterface $resetPasswordRequest): void
    {
        $this->getEntityManager()->remove($resetPasswordRequest);
        $this->getEntityManager()->flush();
    }

    /**
     * Адаптированный метод очистки старых запросов пользователя
     */
    public function removeExpiredResetPasswordRequests(): int
    {
        $time = new \DateTimeImmutable('-1 week'); // или ваш кастомный интервал, бандл обычно берет текущее время

        $query = $this->getEntityManager()
            ->createQuery('DELETE FROM App\Entity\ResetPasswordRequest r WHERE r.expiresAt <= :time')
            ->setParameter('time', $time);

        return $query->execute();
    }
}

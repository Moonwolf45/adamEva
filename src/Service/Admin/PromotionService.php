<?php

declare(strict_types=1);

namespace App\Service\Admin;

use App\Dto\Admin\FormResult;
use App\Entity\Promotion;
use App\Form\PromotionType;
use App\Repository\CategoryRepository;
use App\Repository\PromotionRepository;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

class PromotionService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly FormFactoryInterface $formFactory,
        private readonly PromotionRepository $promotionRepository,
        private readonly CategoryRepository $categoryRepository,
        private readonly ProductRepository $productRepository,
        private readonly TagAwareCacheInterface $tagAwareRedisPool,
    ) {
    }

    public function list(int $page, int $limit): array
    {
        $qb = $this->promotionRepository->createQueryBuilder('p')
            ->orderBy('p.id', 'DESC')
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit);

        $items = $qb->getQuery()->getResult();

        $countQb = $this->promotionRepository->createQueryBuilder('p')
            ->select('COUNT(p.id)');

        return [
            'items' => $items,
            'total' => (int) $countQb->getQuery()->getSingleScalarResult(),
        ];
    }

    /**
     * @throws InvalidArgumentException
     */
    public function create(Request $request): FormResult
    {
        $promotion = new Promotion();
        $form = $this->createForm($promotion);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->validateValue($form, $promotion);

            if ($form->isSynchronized() && !$form->get('value')->getErrors()->count()) {
                $this->entityManager->persist($promotion);
                $this->entityManager->flush();

                $this->tagAwareRedisPool->invalidateTags([PromotionRepository::TAG_ACTIVE]);

                return new FormResult($form, true);
            }
        }

        return new FormResult($form, false);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function update(Request $request, Promotion $promotion): FormResult
    {
        $form = $this->createForm($promotion);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->validateValue($form, $promotion);

            if (!$form->get('value')->getErrors()->count()) {
                $this->entityManager->flush();

                $this->tagAwareRedisPool->invalidateTags([PromotionRepository::TAG_ACTIVE]);

                return new FormResult($form, true);
            }
        }

        return new FormResult($form, false);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function delete(Promotion $promotion): void
    {
        $this->entityManager->remove($promotion);
        $this->entityManager->flush();

        $this->tagAwareRedisPool->invalidateTags([PromotionRepository::TAG_ACTIVE]);
    }

    /**
     * @throws InvalidArgumentException
     */
    private function createForm(Promotion $promotion): FormInterface
    {
        return $this->formFactory->create(PromotionType::class, $promotion, [
            'category_choices' => $this->categoryRepository->findChoicesForFilter(),
            'product_choices' => $this->productRepository->findChoicesForFilter(),
        ]);
    }

    private function validateValue(FormInterface $form, Promotion $promotion): void
    {
        if (Promotion::TYPE_PERCENT === $promotion->getType()
            && ($promotion->getValue() < 1 || $promotion->getValue() > 100)
        ) {
            $form->get('value')->addError(
                new FormError('Процент скидки должен быть от 1 до 100')
            );
        }
    }
}

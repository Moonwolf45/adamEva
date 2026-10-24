<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Product;
use App\Entity\Promotion;
use App\Repository\PromotionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Cache\InvalidArgumentException;

final class PromotionPriceService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly PromotionRepository $promotionRepository,
    ) {
    }

    /**
     * Лучшая активная акция для товара
     * (напрямую по товару или через одну из его категорий).
     *
     * @throws InvalidArgumentException
     */
    public function getPromotionForProduct(Product $product): ?Promotion
    {
        $promotionId = $this->findBestPromotionId($product);

        if (null === $promotionId) {
            return null;
        }

        return $this->entityManager->find(Promotion::class, $promotionId);
    }

    /**
     * Цена с учётом лучшей акции, либо null, если акций нет.
     *
     * @throws InvalidArgumentException
     */
    public function getPromotionPrice(Product $product): ?int
    {
        $promotion = $this->getPromotionForProduct($product);

        if (null === $promotion) {
            return null;
        }

        return $this->applyPromotion($product->getPrice(), $promotion);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function findBestPromotionId(Product $product): ?int
    {
        $snapshot = $this->promotionRepository->findActiveSnapshot();

        $candidateIds = $snapshot['product_map'][$product->getId()] ?? [];

        foreach ($product->getCategories() as $category) {
            $candidateIds = array_merge(
                $candidateIds,
                $snapshot['category_map'][$category->getId()] ?? [],
            );
        }

        $candidateIds = array_unique($candidateIds);

        if ($candidateIds === []) {
            return null;
        }

        // Правило приоритета: выгоднее всего для клиента.
        // Если нужна, например, «последняя созданная» — поменяйте условие на сравнение id.
        $bestId = null;
        $bestDiscount = -1;

        foreach ($candidateIds as $id) {
            $promo = $snapshot['promotions'][$id];
            $discount = $this->discountFor($product->getPrice(), $promo['type'], $promo['value']);

            if ($discount > $bestDiscount) {
                $bestDiscount = $discount;
                $bestId = $id;
            }
        }

        return $bestId;
    }

    public function applyPromotion(int $price, Promotion $promotion): int
    {
        $discount = $this->discountFor($price, $promotion->getType(), $promotion->getValue());

        return max(0, $price - $discount);
    }

    private function discountFor(int $price, string $type, int $value): int
    {
        if (Promotion::TYPE_PERCENT === $type) {
            return (int) floor($price * $value / 100);
        }

        return $value;
    }
}

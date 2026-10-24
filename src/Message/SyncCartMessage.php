<?php

declare(strict_types=1);

namespace App\Message;

/**
 * Полное состояние корзины (snapshot), а не инкрементальное действие —
 * так синк идемпотентен и не зависит от порядка/повторов доставки.
 */
final readonly class SyncCartMessage
{
    /**
     * @param array<int, array{product_id: int, quantity: int, price: int}> $items Ключ — id товара
     */
    public function __construct(
        public string $uuid,
        public array $items,
        public \DateTimeImmutable $syncedAt,
    ) {
    }
}

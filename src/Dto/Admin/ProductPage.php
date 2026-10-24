<?php

declare(strict_types=1);

namespace App\Dto\Admin;

final readonly class ProductPage
{
    public readonly int $maxPages;

    public function __construct(
        /** @var list<array<string, mixed>> */
        public array $items,
        public int $total,
        public int $page,
        public int $limit,
        public ProductFilter $filter,
    ) {
        $this->maxPages = $this->limit > 0 && $this->total > 0 ? (int) ceil($this->total / $this->limit) : 0;
    }
}

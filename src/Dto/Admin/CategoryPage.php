<?php

declare(strict_types=1);

namespace App\Dto\Admin;

final readonly class CategoryPage
{
    public readonly int $maxPages;

    /**
     * @param array $items
     * @param int $total
     * @param int $page
     * @param int $limit
     * @param CategoryFilter $filter
     */
    public function __construct(
        public array $items,
        public int $total,
        public int $page,
        public int $limit,
        public CategoryFilter $filter
    ) {
        $this->maxPages = $this->limit > 0 && $this->total > 0 ? (int) ceil($this->total / $this->limit) : 0;
    }
}

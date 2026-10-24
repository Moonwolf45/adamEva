<?php

declare(strict_types=1);

namespace App\Dto\Admin;

use Symfony\Component\HttpFoundation\Request;

final readonly class ProductFilter
{
    public function __construct(
        public ?int $id,
        public ?string $title,
        public ?int $category,
        public ?string $article,
        public ?bool $status,
        public string $sortBy,
        public string $sortDir,
    ) {}

    public static function fromRequest(Request $request): self
    {
        $query = $request->query;

        return new self(
            id: self::positiveIntOrNull($query->getString('id')),
            title: trim($query->getString('title')) ?: null,
            category: self::positiveIntOrNull($query->getString('category')),
            article: trim($query->getString('article')) ?: null,
            status: match (trim($query->getString('status'))) {
                '0' => false,
                '1' => true,
                default => null,
            },
            sortBy: $query->getAlpha('sort') ?: 'id',
            sortDir: strtolower($query->getAlpha('dir')) === 'asc' ? 'asc' : 'desc',
        );
    }

    private static function positiveIntOrNull(string $value): ?int
    {
        $value = trim($value);

        return ctype_digit($value) && (int) $value > 0 ? (int) $value : null;
    }
}

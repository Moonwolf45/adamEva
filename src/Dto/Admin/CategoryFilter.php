<?php

declare(strict_types=1);

namespace App\Dto\Admin;

use Symfony\Component\HttpFoundation\Request;

final readonly class CategoryFilter
{
    private const array MAIN_VALUES = ['1', 'true', 'on', 'yes'];

    public function __construct(
        public ?int $id,
        public ?string $title,
        public ?int $parent,
        public bool $onlyMain,
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
            parent: self::positiveIntOrNull($query->getString('parent')),
            onlyMain: \in_array($query->getString('onlyMain'), self::MAIN_VALUES, true),
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

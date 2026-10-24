<?php

declare(strict_types=1);

namespace App\Model;

class Breadcrumb
{
    public function __construct(
        private string $label,
        private ?string $url = null,
        private ?string $absoluteUrl = null,
    ) {
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function getAbsoluteUrl(): ?string
    {
        return $this->absoluteUrl;
    }
}

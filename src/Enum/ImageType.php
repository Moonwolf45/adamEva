<?php

declare(strict_types=1);

namespace App\Enum;

enum ImageType: string
{
    case Product = 'products';
    case Banner = 'banners';
    case Promotion = 'promotions';

    public function directory(): string
    {
        return $this->value;
    }
}

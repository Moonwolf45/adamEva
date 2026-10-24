<?php

declare(strict_types=1);

namespace App\Service;

class IpLocation
{
    public function __construct(
        public readonly ?string $country,
        public readonly ?string $city,
        public readonly ?float $latitude,
        public readonly ?float $longitude,
    ) {
    }

    public static function empty(): self
    {
        return new self(null, null, null, null);
    }
}

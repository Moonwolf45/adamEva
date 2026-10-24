<?php

declare(strict_types=1);

namespace App\Message;

final readonly class TrackVisitorMessage
{
    public function __construct(
        public int $visitorId,
        public string $ip,
    ) {
    }
}

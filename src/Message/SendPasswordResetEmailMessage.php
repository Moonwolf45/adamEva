<?php

declare(strict_types=1);

namespace App\Message;

final readonly class SendPasswordResetEmailMessage
{
    public function __construct(
        public string $email,
        public string $resetUrl,
        public string $subject,
        public string $expirationMessage,
    ) {
    }
}

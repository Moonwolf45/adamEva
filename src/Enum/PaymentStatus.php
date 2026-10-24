<?php

declare(strict_types=1);

namespace App\Enum;

enum PaymentStatus: string
{
    case PENDING = 'pending';
    case PROCESSING = 'processing';
    case PAID = 'paid';
    case FAILED = 'failed';
    case REFUNDED = 'refunded';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Ожидание оплаты',
            self::PROCESSING => 'Оплата в процессе',
            self::PAID => 'Оплачено',
            self::FAILED => 'Оплата не удалась',
            self::REFUNDED => 'Возврат средств',
        };
    }
}

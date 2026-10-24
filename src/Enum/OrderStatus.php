<?php

declare(strict_types=1);

namespace App\Enum;

enum OrderStatus: string
{
    case NEW = 'new';
    case PROCESSING = 'processing';
    case SHIPPED = 'shipped';
    case DELIVERED = 'delivered';
    case CANCELLED = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::NEW => 'Новый',
            self::PROCESSING => 'В процессе',
            self::SHIPPED => 'Отправлен',
            self::DELIVERED => 'Доставлен',
            self::CANCELLED => 'Отменен',
        };
    }
}

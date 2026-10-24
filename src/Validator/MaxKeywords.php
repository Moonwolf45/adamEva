<?php

declare(strict_types=1);

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * Проверяет количество слов, разделённых заданным разделителем (по умолчанию запятая).
 * Пустые элементы (после обрезки пробелов) не учитываются.
 */
#[\Attribute(\Attribute::TARGET_PROPERTY | \Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
final class MaxKeywords extends Constraint
{
    /**
     * @param int $max максимально допустимое количество слов
     * @param string $delimiter разделитель слов
     * @param string $message сообщение об ошибке
     * @param string[]|null $groups
     */
    public function __construct(
        public int $max = 8,
        public string $delimiter = ',',
        public string $message = 'Количество ключевых слов не должно превышать {{ max }}. Введено: {{ count }}.',
        ?array $groups = null,
        mixed $payload = null,
    ) {
        parent::__construct([], $groups, $payload);
    }
}

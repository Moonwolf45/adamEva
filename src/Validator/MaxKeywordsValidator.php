<?php

declare(strict_types=1);

namespace App\Validator;

use Override;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

final class MaxKeywordsValidator extends ConstraintValidator
{
    #[Override]
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof MaxKeywords) {
            throw new UnexpectedTypeException($constraint, MaxKeywords::class);
        }

        if ($value === null || $value === '') {
            return;
        }

        if (!\is_string($value)) {
            throw new UnexpectedValueException($value, 'string');
        }

        $keywords = explode($constraint->delimiter, $value)
            |> (fn($x) => array_map(trim(...), $x))
            |> (fn($x) => array_filter($x, static fn(string $keyword): bool => $keyword !== ''));

        $count = \count($keywords);

        if ($count > $constraint->max) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ count }}', (string) $count)
                ->setParameter('{{ max }}', (string) $constraint->max)
                ->addViolation();
        }
    }
}

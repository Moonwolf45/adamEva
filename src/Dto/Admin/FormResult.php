<?php

declare(strict_types=1);

namespace App\Dto\Admin;

use Symfony\Component\Form\FormInterface;

final readonly class FormResult
{
    /**
     * @param FormInterface<mixed> $form
     */
    public function __construct(
        public FormInterface $form,
        public bool $saved,
    ) {
    }
}

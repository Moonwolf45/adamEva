<?php

declare(strict_types=1);

namespace App\Form;

use Override;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Чекбокс, отображаемый как переключатель (Bootstrap 5 switch).
 *
 * @extends AbstractType<bool>
 */
class SwitchType extends AbstractType
{
    /**
     * @param OptionsResolver $resolver
     */
    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'attr' => [
                'role' => 'switch',
            ],
            'label_attr' => [
                'class' => 'checkbox-switch',
            ],
        ]);
    }

    #[Override]
    public function getParent(): ?string
    {
        return CheckboxType::class;
    }
}

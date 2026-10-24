<?php

declare(strict_types=1);

namespace App\Form;

use Override;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;


class ImageType extends AbstractType
{
    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'label' => 'Изображение',
            'id' => 'image',
            'help' => 'JPG или PNG, до 5МБ',
            'constraints' => [
                new Assert\File(
                    maxSize: '5M',
                    mimeTypes: ['image/jpeg', 'image/png'],
                    maxSizeMessage: 'Размер файла не может превышать {{ limit }}.',
                ),
            ],
        ]);
    }

    #[Override]
    public function getParent(): ?string
    {
        return FileType::class;
    }
}

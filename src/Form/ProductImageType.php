<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\ProductImage;
use Override;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class ProductImageType extends AbstractType
{
    public const ACCEPTED_MIME_TYPES = ['image/jpeg', 'image/png'];

    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('image', FileType::class, [
                'required' => false,
                'label' => false,
                'mapped' => false,
                'constraints' => [
                    new Assert\File(
                        maxSize: '5M',
                        mimeTypes: self::ACCEPTED_MIME_TYPES,
                        maxSizeMessage: 'Размер файла не может превышать {{ limit }}.',
                    ),
                ],
                'attr' => [
                    'class' => 'js-product-image-file',
                    'accept' => implode(',', self::ACCEPTED_MIME_TYPES),
                ],
            ])
            ->add('delete', CheckboxType::class, [
                'required' => false,
                'label' => false,
                'mapped' => false,
            ]);
    }

    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ProductImage::class,
        ]);
    }

}

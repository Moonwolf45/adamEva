<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Category;
use App\Entity\Product;
use App\Validator\MaxKeywords;
use Doctrine\ORM\EntityManagerInterface;
use Override;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class ProductType extends AbstractType
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * @param array<string, mixed> $options
     */
    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $categoryChoices = $options['category_choices'];
        $categoryIds = array_map(static fn (array $item): int => $item['id'], $categoryChoices);

        $categoryLabels = [];
        foreach ($categoryChoices as $item) {
            $categoryLabels[$item['id']] = str_repeat('— ', $item['depth']) . $item['title'];
        }

        $builder
            ->add('title', TextType::class, [
                'required' => true,
                'label' => 'Название',
                'constraints' => new Assert\NotBlank(message: 'Название не может быть пустым'),
            ])
            // SEO-группа: главная категория + meta-поля (compound → <fieldset> с legend).
            ->add('seo', null, [
                'label' => 'SEO',
                'compound' => true,
                'row_attr' => ['class' => 'border rounded p-3 mb-3'],
            ])
            ->add('categories', ChoiceType::class, [
                'label' => 'Категории',
                'required' => false,
                'multiple' => true,
                'expanded' => false,
                'placeholder' => '— Выберите категории —',
                'choices' => $categoryIds,
                'choice_label' => static fn (int $id): string => $categoryLabels[$id] ?? (string) $id,
                'choice_translation_domain' => false,
                'attr' => [
                    'class' => 'select2-product-categories',
                ],
            ])
            ->add('article', TextType::class, [
                'required' => true,
                'label' => 'Артикул',
                'constraints' => new Assert\NotBlank(message: 'Артикул не может быть пустым'),
            ])
            ->add('model', TextType::class, [
                'required' => false,
                'label' => 'Модель',
            ])
            ->add('price', IntegerType::class, [
                'required' => true,
                'label' => 'Цена',
                'constraints' => new Assert\NotBlank(message: 'Цена не может быть пустой'),
            ])
            ->add('main_image', ImageType::class, [
                'required' => false,
                'label' => 'Главная картинка',
                'mapped' => false,
            ])
            ->add('images', CollectionType::class, [
                'entry_type' => ProductImageType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'prototype' => true,
                'by_reference' => false,
                'label' => 'Дополнительные картинки',
            ])
            ->add('description', TextareaType::class, [
                'required' => true,
                'label' => 'Описание',
            ])
            ->add('status', SwitchType::class, [
                'label' => 'Статус',
            ]);

        // Внутренности SEO-группы.
        $builder->get('seo')
            ->add('meta_keywords', TextType::class, [
                'required' => false,
                'label' => 'Meta-ключевые слова',
                'constraints' => new MaxKeywords(max: 8),
            ])
            ->add('meta_description', TextType::class, [
                'required' => false,
                'label' => 'Meta-описание',
                'constraints' => new Assert\Length(max: 155),
            ])
            ->add('mainCategory', ChoiceType::class, [
                'required' => true,
                'label' => 'Главная категория',
                'placeholder' => '— Выберите категорию —',
                'choices' => $categoryIds,
                'choice_label' => static fn (int $id): string => $categoryLabels[$id] ?? (string) $id,
                'choice_translation_domain' => false,
                'attr' => [
                    'class' => 'select2-product-main-category',
                ],
            ])
        ;

        // mainCategory хранится как сущность, ChoiceType оперирует id.
        $builder->get('seo')->get('mainCategory')->addModelTransformer(
            new CallbackTransformer(
                static fn (?Category $category): ?int => $category?->getId(),
                fn (int|string|null $id): ?Category => null !== $id && '' !== $id
                    ? $this->entityManager->find(Category::class, (int) $id)
                    : null,
            )
        );

        // main_image хранится как строка-путь, в форме — файл.
        $builder->get('main_image')->addModelTransformer(
            new CallbackTransformer(
                static fn (?string $path): File => new File($path ?? '', false),
                static fn (File|string|null $file): ?string => $file instanceof File && $file->getPathname() !== ''
                    ? $file->getPathname()
                    : null,
            )
        );
    }

    /**
     * @param OptionsResolver $resolver
     */
    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Product::class,
            'category_choices' => [],
        ]);

        $resolver->setAllowedTypes('category_choices', 'array');
    }
}

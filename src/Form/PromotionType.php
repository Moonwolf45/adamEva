<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Promotion;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Constraints\Range;

class PromotionType extends AbstractType
{
    /**
     * @param array<string, mixed> $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $categoryChoices = $options['category_choices'];
        $categoryIds = array_map(static fn (array $item): int => $item['id'], $categoryChoices);

        $categoryLabels = [];
        foreach ($categoryChoices as $item) {
            $categoryLabels[$item['id']] = str_repeat('— ', $item['depth']) . $item['title'];
        }

        $productChoices = $options['product_choices'];
        $productIds = array_keys($productChoices);

        $builder
            ->add('title', TextType::class, [
                'required' => true,
                'label' => 'Название',
                'constraints' => new Assert\NotBlank(message: 'Название не может быть пустым'),
            ])
            ->add('type', ChoiceType::class, [
                'label' => 'Тип скидки',
                'required' => true,
                'choices' => [
                    'Процент (%)' => Promotion::TYPE_PERCENT,
                    'Фиксированная сумма' => Promotion::TYPE_FIXED,
                ],
                'expanded' => true,
                'multiple' => false,
                'constraints' => new Choice(
                    choices: [Promotion::TYPE_PERCENT, Promotion::TYPE_FIXED],
                    message: 'Выберите тип скидки',
                ),
            ])
            ->add('value', IntegerType::class, [
                'required' => true,
                'label' => 'Значение',
                'constraints' => [
                    new Assert\NotBlank(message: 'Значение скидки не может быть пустым'),
                    new Assert\GreaterThanOrEqual(
                        value: 1,
                        message: 'Значение скидки должно быть не меньше 1',
                    ),
                ],
            ])
            ->add('categories', ChoiceType::class, [
                'label' => 'Категории',
                'required' => false,
                'multiple' => true,
                'expanded' => false,
                'placeholder' => '— Без категорий —',
                'choices' => $categoryIds,
                'choice_label' => static fn (int $id): string => $categoryLabels[$id] ?? (string) $id,
                'choice_translation_domain' => false,
                'attr' => [
                    'class' => 'select2-promotion-categories',
                ],
            ])
            ->add('products', ChoiceType::class, [
                'label' => 'Товары',
                'required' => false,
                'multiple' => true,
                'expanded' => false,
                'placeholder' => '— Без товаров —',
                'choices' => $productIds,
                'choice_label' => static fn (int $id): string => $productChoices[$id] ?? (string) $id,
                'choice_translation_domain' => false,
                'attr' => [
                    'class' => 'select2-promotion-products',
                ],
            ])
            ->add('starts_at', DateTimeType::class, [
                'label' => 'Начало акции',
                'required' => false,
                'widget' => 'single_text',
                'input' => 'datetime_immutable',
            ])
            ->add('ends_at', DateTimeType::class, [
                'label' => 'Окончание акции',
                'required' => false,
                'widget' => 'single_text',
                'input' => 'datetime_immutable',
            ])
            ->add('status', SwitchType::class, [
                'label' => 'Статус',
            ]);
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Promotion::class,
            'category_choices' => [],
            'product_choices' => [],
        ]);

        $resolver->setAllowedTypes('category_choices', 'array');
        $resolver->setAllowedTypes('product_choices', 'array');
    }
}

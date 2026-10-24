<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Category;
use App\Validator\MaxKeywords;
use Doctrine\ORM\EntityManagerInterface;
use Override;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @extends AbstractType<Category>
 */
class CategoryType extends AbstractType
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
        $parentChoices = $options['parent_choices'];
        $parentIds = array_map(static fn (array $item): int => $item['id'], $parentChoices);

        $parentLabels = [];
        foreach ($parentChoices as $item) {
            $parentLabels[$item['id']] = str_repeat('— ', $item['depth']) . $item['title'];
        }

        $builder
            ->add('title', TextType::class, [
                'required' => true,
                'label' => 'Название',
                'constraints' => new Assert\NotBlank(message: 'Название не может быть пустым'),
            ])
            ->add('meta_keywords', TextType::class, [
                'required' => false,
                'label' => 'Meta-ключевые слова',
                'constraints' => new MaxKeywords(max: 8),
            ])
            ->add('meta_description', TextType::class, [
                'required' => false,
                'label' => 'Meta-описание',
                'constraints' => new Assert\Length(max: 115),
            ])
            ->add('parent', ChoiceType::class, [
                'label' => 'Родительская категория',
                'required' => false,
                'placeholder' => '— Без родителя —',
                'choices' => $parentIds,
                'choice_label' => static fn (int $id): string => $parentLabels[$id] ?? (string) $id,
                'choice_translation_domain' => false,
                'attr' => [
                    'class' => 'select2-category-parent',
                ],
            ])
            ->add('status', SwitchType::class, [
                'label' => 'Статус',
            ])
        ;

        // Category хранит parent как сущность, а ChoiceType оперирует id.
        $builder->get('parent')->addModelTransformer(
            new CallbackTransformer(
                static fn (?Category $parent): ?int => $parent?->getId(),
                fn (int|string|null $id): ?Category => null !== $id && '' !== $id
                    ? $this->entityManager->find(Category::class, (int) $id)
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
            'data_class' => Category::class,
            'parent_choices' => [],
        ]);

        $resolver->setAllowedTypes('parent_choices', 'array');
    }
}

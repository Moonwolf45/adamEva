<?php

declare(strict_types=1);

namespace App\Service\Admin;

use App\Dto\Admin\CategoryFilter;
use App\Dto\Admin\CategoryPage;
use App\Dto\Admin\FormResult;
use App\Entity\Category;
use App\Form\CategoryType;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

final class CategoryService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly FormFactoryInterface $formFactory,
        private readonly CategoryRepository $categoryRepository,
        private readonly TagAwareCacheInterface $cache
    ) {
    }

    public function list(
        int $page,
        int $limit,
        CategoryFilter $filter
    ): CategoryPage {
        $result = $this->categoryRepository->findPaginatedWithFilter(
            $filter,
            $page,
            $limit
        );

        return new CategoryPage(
            items: $result['items'],
            total: (int) $result['total'],
            page: $page,
            limit: $limit,
            filter: $filter
        );
    }

    /**
     * Список категорий для фильтра по родителю.
     *
     * @return array<int, string, int|null, array>
     *
     * @throws InvalidArgumentException
     */
    public function parentChoices(): array
    {
        return $this->categoryRepository->findChoicesForFilter();
    }

    /**
     * @throws InvalidArgumentException
     */
    public function create(Request $request): FormResult
    {
        $category = new Category();
        $form = $this->formFactory->create(CategoryType::class, $category, [
            'parent_choices' => $this->parentChoices(),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($category);
            $this->entityManager->flush();

            $this->cache->invalidateTags(['categoriesTreeList']);

            return new FormResult($form, true);
        }

        return new FormResult($form, false);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function update(Request $request, Category $category): FormResult
    {
        $form = $this->formFactory->create(CategoryType::class, $category, [
            'parent_choices' => $this->parentChoices(),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();

            $this->cache->invalidateTags(['categoriesTreeList']);

            return new FormResult($form, true);
        }

        return new FormResult($form, false);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function delete(Category $category): bool
    {
        $this->entityManager->remove($category);
        $this->entityManager->flush();

        $this->cache->invalidateTags(['categoriesTreeList']);

        return true;
    }
}

<?php

declare(strict_types=1);

namespace App\Service\Admin;

use App\Dto\Admin\FormResult;
use App\Dto\Admin\ProductFilter;
use App\Dto\Admin\ProductPage;
use App\Entity\Product;
use App\Entity\ProductImage;
use App\Enum\ImageType;
use App\Form\ProductType;
use App\Repository\CategoryRepository;
use App\Repository\ProductRepository;
use App\Service\ImageProcessor;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Cache\InvalidArgumentException;
use Random\RandomException;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Request;

final class ProductService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly FormFactoryInterface $formFactory,
        private readonly ProductRepository $productRepository,
        private readonly CategoryRepository $categoryRepository,
        private readonly ImageProcessor $imageProcessor,
    ) {
    }

    public function list(int $page, int $limit, ProductFilter $filter): ProductPage
    {
        $result = $this->productRepository->findPaginatedWithFilter($filter, $page, $limit);

        return new ProductPage(
            items: $result['items'],
            total: (int) $result['total'],
            page: $page,
            limit: $limit,
            filter: $filter,
        );
    }

    /**
     * Список категорий для фильтра по категории.
     *
     * @return array<int, array{id: int, title: string, depth: int}>
     *
     * @throws InvalidArgumentException
     */
    public function categoryChoices(): array
    {
        return $this->categoryRepository->findChoicesForFilter();
    }

    /**
     * @throws InvalidArgumentException
     * @throws RandomException
     */
    public function create(Request $request): FormResult
    {
        $product = new Product();
        $form = $this->createForm($product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->syncMainCategory($product);

            $this->entityManager->persist($product);
            $this->entityManager->flush();

            $this->processMainImage($product, $form);
            $this->processAdditionalImages($product, $form);
            $this->entityManager->flush();

            return new FormResult($form, true);
        }

        return new FormResult($form, false);
    }

    /**
     * @throws InvalidArgumentException
     * @throws RandomException
     */
    public function update(Request $request, Product $product): FormResult
    {
        $form = $this->createForm($product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->processMainImage($product, $form);
            $this->processAdditionalImages($product, $form);
            $this->syncMainCategory($product);

            $this->entityManager->flush();

            return new FormResult($form, true);
        }

        return new FormResult($form, false);
    }

    /**
     * Мягкое удаление (Gedmo SoftDeleteable).
     */
    public function delete(Product $product): void
    {
        $product->setDeletedAt(new \DateTimeImmutable());

        $this->entityManager->persist($product);
        $this->entityManager->flush();
    }

    private function createForm(Product $product): FormInterface
    {
        return $this->formFactory->create(ProductType::class, $product, [
            'category_choices' => $this->categoryChoices(),
        ]);
    }

    /**
     * Загрузка главной картинки: новая заменяет старую,
     * либо явное удаление через hidden-поле form[main_image_remove].
     *
     * @throws RandomException
     */
    private function processMainImage(Product $product, FormInterface $form): void
    {
        if (null === $product->getId()) {
            return;
        }

        $file = $form->get('main_image')->getData();
        $hasNewFile = $file instanceof File
            && '' !== $file->getPathname()
            && (new SplFileInfo($file->getPathname()))->getSize() > 0;

        // flag из hidden-поля (см. _form.html.twig)
        $remove = null !== $form->get('main_image_remove')
            && '1' === $form->get('main_image_remove')->getData();

        if ($remove) {
            $this->imageProcessor->delete((string) $product->getImage());
            $product->setImage(null);
        }

        if ($hasNewFile) {
            $oldImage = $product->getImage();

            $product->setImage($this->imageProcessor->upload(
                $file,
                ImageType::Product,
                $product->getId(),
                $product->getSlug(),
            ));

            if (null !== $oldImage && $oldImage !== $product->getImage()) {
                $this->imageProcessor->delete($oldImage);
            }
        }
    }

    /**
     * Дополнительные картинки: новые файлы — на диск,
     * помеченные delete — файл + запись удаляются.
     *
     * @throws RandomException
     */
    private function processAdditionalImages(Product $product, FormInterface $form): void
    {
        if (null === $product->getId()) {
            return;
        }

        foreach ($form->get('images') as $entry) {
            /** @var ProductImage $image */
            $image = $entry->getData();
            $delete = (bool) $entry->get('delete')->getData();
            $file = $entry->get('image')->getData();
            $hasFile = $file instanceof File
                && '' !== $file->getPathname()
                && (new \SplFileInfo($file->getPathname()))->getSize() > 0;

            if ($delete) {
                if (null !== $image->getId()) {
                    $this->imageProcessor->delete((string) $image->getImage());
                    $product->removeProductImage($image);
                    $this->entityManager->remove($image);
                }

                continue;
            }

            if ($hasFile) {
                $image->setImage(
                    $this->imageProcessor->upload($file, ImageType::Product, $product->getId()),
                );
                $product->addProductImage($image);
                $this->entityManager->persist($image);

                continue;
            }

            // Новая строка без выбранного файла — выкидываем,
            // чтобы не создавать запись с пустым не-null-полем image.
            if (null === $image->getId()) {
                $product->removeProductImage($image);
            }
        }
    }

    /**
     * Главная категория всегда присутствует в ManyToMany-списке категорий.
     */
    private function syncMainCategory(Product $product): void
    {
        $mainCategory = $product->getMainCategory();

        if (null !== $mainCategory && !$product->getCategories()->contains($mainCategory)) {
            $product->addCategory($mainCategory);
        }
    }
}

<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Dto\Admin\ProductFilter;
use App\Entity\Product;
use App\Service\Admin\ProductService;
use App\Service\BreadcrumbsService;
use Psr\Cache\InvalidArgumentException;
use Random\RandomException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/products')]
final class ProductsController extends AbstractController
{
    private const int PER_PAGE = 20;

    /**
     * @throws InvalidArgumentException
     */
    #[Route('/', name: 'admin_products_index', defaults: ['page' => 1])]
    #[Route('/page/{page}', name: 'admin_products_paginated_index', requirements: ['page' => '\d+'])]
    public function index(
        Request $request,
        int $page,
        ProductService $service,
        BreadcrumbsService $breadcrumbs,
    ): Response {
        $breadcrumbs
            ->addRoute('Главная', 'admin_default_index')
            ->add('Товары');

        $filter = ProductFilter::fromRequest($request);
        $page = max(1, $page);

        $result = $service->list($page, self::PER_PAGE, $filter);

        if ($result->maxPages > 0 && $page > $result->maxPages) {
            throw $this->createNotFoundException('Страница не найдена');
        }

        return $this->render('admin/products/index.html.twig', [
            'products' => $result->items,
            'total' => $result->total,
            'currentPage' => $result->page,
            'maxPages' => $result->maxPages,
            'perPage' => $result->limit,
            'filter' => $filter,
            'categoryChoices' => $service->categoryChoices(),
        ]);
    }

    /**
     * @throws InvalidArgumentException
     * @throws RandomException
     */
    #[Route('/new', name: 'admin_products_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        ProductService $service,
        BreadcrumbsService $breadcrumbs,
    ): Response {
        $breadcrumbs
            ->addRoute('Главная', 'admin_default_index')
            ->addRoute('Товары', 'admin_products_index')
            ->add('Создание товара');

        $result = $service->create($request);

        if ($result->saved) {
            return $this->redirectToRoute('admin_products_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/products/new.html.twig', [
            'product' => $result->form->getData(),
            'form' => $result->form,
        ]);
    }

    #[Route('/{id}', name: 'admin_products_show', methods: ['GET'])]
    public function show(
        Product $product,
        BreadcrumbsService $breadcrumbs,
    ): Response {
        $breadcrumbs
            ->addRoute('Главная', 'admin_default_index')
            ->addRoute('Товары', 'admin_products_index')
            ->add('Просмотр товара ' . $product->getTitle());

        return $this->render('admin/products/show.html.twig', [
            'product' => $product,
        ]);
    }

    /**
     * @throws InvalidArgumentException
     * @throws RandomException
     */
    #[Route('/{id}/edit', name: 'admin_products_edit', methods: ['GET', 'POST'])]
    public function edit(
        Product $product,
        Request $request,
        ProductService $service,
        BreadcrumbsService $breadcrumbs,
    ): Response {
        $breadcrumbs
            ->addRoute('Главная', 'admin_default_index')
            ->addRoute('Товары', 'admin_products_index')
            ->add('Редактирование товара ' . $product->getTitle());

        $result = $service->update($request, $product);

        if ($result->saved) {
            return $this->redirectToRoute('admin_products_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/products/edit.html.twig', [
            'product' => $product,
            'form' => $result->form,
        ]);
    }

    /**
     * Мягкое удаление товара (Gedmo SoftDeleteable).
     */
    #[Route('/{id}', name: 'admin_products_delete', methods: ['POST'])]
    public function delete(Request $request, Product $product, ProductService $service): Response
    {
        if ($this->isCsrfTokenValid('delete' . (string) $product->getId(), $request->getPayload()->getString('_token'))) {
            $service->delete($product);
        }

        return $this->redirectToRoute('admin_products_index', [], Response::HTTP_SEE_OTHER);
    }
}

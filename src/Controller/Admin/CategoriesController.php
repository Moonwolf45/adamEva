<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Dto\Admin\CategoryFilter;
use App\Entity\Category;
use App\Service\Admin\CategoryService;
use App\Service\BreadcrumbsService;
use Psr\Cache\InvalidArgumentException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/categories')]
final class CategoriesController extends AbstractController
{
    private const int PER_PAGE = 20;

    /**
     * @throws InvalidArgumentException
     */
    #[Route('/', name: 'admin_categories_index', defaults: ['page' => 1])]
    #[Route('/page/{page}', name: 'admin_categories_paginated_index', requirements: ['page' => '\d+'])]
    public function index(
        Request $request,
        int $page,
        CategoryService $service,
        BreadcrumbsService $breadcrumbs,
    ): Response {
        $breadcrumbs
            ->addRoute('Главная', 'admin_default_index')
            ->add('Категории');

        $filter = CategoryFilter::fromRequest($request);
        $page = max(1, $page);

        $result = $service->list($page, self::PER_PAGE, $filter);

        if ($result->maxPages > 0 && $page > $result->maxPages) {
            throw $this->createNotFoundException('Страница не найдена');
        }

        if ($result->maxPages > 0 && $page > $result->maxPages) {
            throw $this->createNotFoundException('Страница не найдена');
        }

        return $this->render('admin/categories/index.html.twig', [
            'categories' => $result->items,
            'total' => $result->total,
            'currentPage' => $result->page,
            'maxPages' => $result->maxPages,
            'perPage' => $result->limit,
            'filter' => $filter,
            'parentChoices' => $service->parentChoices(),
        ]);
    }

    /**
     * @throws InvalidArgumentException
     */
    #[Route('/new', name: 'admin_categories_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        CategoryService $service,
        BreadcrumbsService $breadcrumbs,
    ): Response
    {
        $breadcrumbs
            ->addRoute('Главная', 'admin_default_index')
            ->addRoute('Категории', 'admin_categories_index')
            ->add('Создание категории');

        $result = $service->create($request);

        if ($result->saved) {
            return $this->redirectToRoute('admin_categories_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/categories/new.html.twig', [
            'category' => $result->form->getData(),
            'form' => $result->form,
        ]);
    }

    #[Route('/{id}', name: 'admin_categories_show', methods: ['GET'])]
    public function show(
        Category $category,
        BreadcrumbsService $breadcrumbs,
    ): Response
    {
        $breadcrumbs
            ->addRoute('Главная', 'admin_default_index')
            ->addRoute('Категории', 'admin_categories_index')
            ->add('Просмотр категории ' . $category->getTitle());

        return $this->render('admin/categories/show.html.twig', [
            'category' => $category,
        ]);
    }

    /**
     * @throws InvalidArgumentException
     */
    #[Route('/{id}/edit', name: 'admin_categories_edit', methods: ['GET', 'POST'])]
    public function edit(
        Category $category,
        Request $request,
        CategoryService $service,
        BreadcrumbsService $breadcrumbs,
    ): Response
    {
        $breadcrumbs
            ->addRoute('Главная', 'admin_default_index')
            ->addRoute('Категории', 'admin_categories_index')
            ->add('Редактирование категории ' . $category->getTitle());

        $result = $service->update($request, $category);

        if ($result->saved) {
            return $this->redirectToRoute('admin_categories_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/categories/edit.html.twig', [
            'category' => $category,
            'form' => $result->form,
        ]);
    }

    /**
     * @throws InvalidArgumentException
     */
    #[Route('/{id}', name: 'admin_categories_delete', methods: ['POST'])]
    public function delete(Request $request, Category $category, CategoryService $service): Response
    {
        if ($this->isCsrfTokenValid('delete' . (string) $category->getId(), $request->getPayload()->getString('_token'))) {
            $service->delete($category);
        }

        return $this->redirectToRoute('admin_categories_index', [], Response::HTTP_SEE_OTHER);
    }
}

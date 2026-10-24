<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Promotion;
use App\Service\Admin\PromotionService;
use App\Service\BreadcrumbsService;
use Psr\Cache\InvalidArgumentException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/promotions')]
final class PromotionsController extends AbstractController
{
    private const int PER_PAGE = 20;

    #[Route('/', name: 'admin_promotions_index', defaults: ['page' => 1])]
    #[Route('/page/{page}', name: 'admin_promotions_paginated_index', requirements: ['page' => '\d+'])]
    public function index(
        int $page,
        PromotionService $service,
        BreadcrumbsService $breadcrumbs,
    ): Response {
        $breadcrumbs
            ->addRoute('Главная', 'admin_default_index')
            ->add('Акции');

        $page = max(1, $page);
        $result = $service->list($page, self::PER_PAGE);

        $maxPages = (int) ceil($result['total'] / self::PER_PAGE);
        if ($maxPages > 0 && $page > $maxPages) {
            throw $this->createNotFoundException('Страница не найдена');
        }

        return $this->render('admin/promotions/index.html.twig', [
            'promotions' => $result['items'],
            'total' => $result['total'],
            'currentPage' => $page,
            'maxPages' => $maxPages,
            'perPage' => self::PER_PAGE,
        ]);
    }

    /**
     * @throws InvalidArgumentException
     */
    #[Route('/new', name: 'admin_promotions_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        PromotionService $service,
        BreadcrumbsService $breadcrumbs,
    ): Response {
        $breadcrumbs
            ->addRoute('Главная', 'admin_default_index')
            ->addRoute('Акции', 'admin_promotions_index')
            ->add('Создание акции');

        $result = $service->create($request);

        if ($result->saved) {
            return $this->redirectToRoute('admin_promotions_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/promotions/new.html.twig', [
            'promotion' => $result->form->getData(),
            'form' => $result->form,
        ]);
    }

    #[Route('/{id}', name: 'admin_promotions_show', methods: ['GET'])]
    public function show(
        Promotion $promotion,
        BreadcrumbsService $breadcrumbs,
    ): Response {
        $breadcrumbs
            ->addRoute('Главная', 'admin_default_index')
            ->addRoute('Акции', 'admin_promotions_index')
            ->add('Просмотр акции ' . $promotion->getTitle());

        return $this->render('admin/promotions/show.html.twig', [
            'promotion' => $promotion,
        ]);
    }

    /**
     * @throws InvalidArgumentException
     */
    #[Route('/{id}/edit', name: 'admin_promotions_edit', methods: ['GET', 'POST'])]
    public function edit(
        Promotion $promotion,
        Request $request,
        PromotionService $service,
        BreadcrumbsService $breadcrumbs,
    ): Response {
        $breadcrumbs
            ->addRoute('Главная', 'admin_default_index')
            ->addRoute('Акции', 'admin_promotions_index')
            ->add('Редактирование акции ' . $promotion->getTitle());

        $result = $service->update($request, $promotion);

        if ($result->saved) {
            return $this->redirectToRoute('admin_promotions_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/promotions/edit.html.twig', [
            'promotion' => $promotion,
            'form' => $result->form,
        ]);
    }

    #[Route('/{id}', name: 'admin_promotions_delete', methods: ['POST'])]
    public function delete(Request $request, Promotion $promotion, PromotionService $service): Response
    {
        if ($this->isCsrfTokenValid('delete' . (string) $promotion->getId(), $request->getPayload()->getString('_token'))) {
            $service->delete($promotion);
        }

        return $this->redirectToRoute('admin_promotions_index', [], Response::HTTP_SEE_OTHER);
    }
}

<?php

declare(strict_types=1);

namespace App\Controller\Profile;

use App\Service\BreadcrumbsService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

#[Route('/profile')]
final class OrderController extends AbstractController
{
    #[Route('/', name: 'profile_order_index')]
    public function index(BreadcrumbsService $breadcrumbs): Response
    {
        $breadcrumbs->add('Главная');

        return $this->render('profile/order/index.html.twig');
    }

}

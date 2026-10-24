<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Service\BreadcrumbsService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

#[Route('/admin')]
final class DefaultController extends AbstractController
{
    #[Route('/', name: 'admin_default_index')]
    public function index(BreadcrumbsService $breadcrumbs): Response
    {
        $breadcrumbs->add('Главная');

        return $this->render('admin/default/index.html.twig');
    }

    #[Route('/login', name: 'admin_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('admin/auth/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }


    #[Route('/logout', name: 'admin_logout')]
    public function logout(Security $security): Response
    {
        $security->logout();

        return $this->redirect('/');
    }
}

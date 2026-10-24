<?php

declare(strict_types=1);

namespace App\Extension;

use App\Service\BreadcrumbsService;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class BreadcrumbsExtension extends AbstractExtension
{
    public function __construct(
        private BreadcrumbsService $breadcrumbsService,
    ) {
    }

    /**
     * @return list<TwigFunction>
     */
    #[\Override]
    public function getFunctions(): array
    {
        return [
            new TwigFunction(
                'render_breadcrumbs',
                [$this, 'renderBreadcrumbs'],
                ['is_safe' => ['html'], 'needs_environment' => true]
            ),
        ];
    }

    /**
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws LoaderError
     */
    public function renderBreadcrumbs(Environment $twig, string $template = '_partials/breadcrumbs.html.twig'): string
    {
        return $twig->render($template, [
            'breadcrumbs' => $this->breadcrumbsService->getItems(),
        ]);
    }
}

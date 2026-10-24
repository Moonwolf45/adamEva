<?php

declare(strict_types=1);

namespace App\Service;

use App\Model\Breadcrumb;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Service\ResetInterface;

class BreadcrumbsService implements ResetInterface
{
    /**
     * @var list<Breadcrumb>
     */
    private array $breadcrumbs = [];

    public function __construct(
        private UrlGeneratorInterface $urlGenerator,
    ) {
    }

    // Добавление крошки по названию и роуту
    /**
     * @param array<string, mixed> $parameters
     */
    public function addRoute(string $label, string $route, array $parameters = []): self
    {
        $url = $this->urlGenerator->generate($route, $parameters);
        $absoluteUrl = $this->urlGenerator->generate($route, $parameters, UrlGeneratorInterface::ABSOLUTE_URL);
        $this->breadcrumbs[] = new Breadcrumb($label, $url, $absoluteUrl);

        return $this;
    }

    // Добавление крошки с готовым URL или без ссылки (для текущей страницы)
    public function add(string $label, ?string $url = null): self
    {
        foreach ($this->breadcrumbs as $existing) {
            if (null !== $url && $existing->getUrl() === $url) {
                return $this;
            }
        }
        $this->breadcrumbs[] = new Breadcrumb($label, $url);

        return $this;
    }

    // Возврат всех накопленных крошек
    /**
     * @return list<Breadcrumb>
     */
    public function getItems(): array
    {
        return $this->breadcrumbs;
    }

    #[\Override]
    public function reset(): void
    {
        $this->breadcrumbs = [];
    }
}

<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Service\CartUuidService;
use Random\RandomException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class CartUuidSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly CartUuidService $cartUuidService,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest', 1024],
            KernelEvents::RESPONSE => ['onKernelResponse', 1024],
            // ниже по приоритету, чем ErrorListener (-128): кукa попадёт даже на 404/500
            KernelEvents::EXCEPTION => ['onKernelException', -256],
        ];
    }

    /**
     * @throws RandomException
     */
    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $this->cartUuidService->getOrCreate($event->getRequest());
    }

    public function onKernelResponse(ResponseEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $this->cartUuidService->attachCookieIfNew($event->getRequest(), $event->getResponse());
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        if (!$event->isMainRequest() || null === $response = $event->getResponse()) {
            return;
        }

        $this->cartUuidService->attachCookieIfNew($event->getRequest(), $response);
    }
}

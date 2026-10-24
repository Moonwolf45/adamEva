<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Message\SyncCartMessage;
use App\Service\CartService;
use App\Service\CartUuidService;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\TerminateEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * В конце запроса (kernel.terminate — после сохранения сессии и отправки ответа)
 * отправляет состояние корзины в очередь, если она менялась в этом запросе.
 * Один запрос = максимум одно сообщение.
 */
class CartSyncSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly CartService $cartService,
        private readonly MessageBusInterface $bus,
        private readonly LoggerInterface $logger,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::TERMINATE => ['onKernelTerminate'],
        ];
    }

    public function onKernelTerminate(TerminateEvent $event): void
    {
        if (!$event->isMainRequest() || !$this->cartService->isDirty()) {
            return;
        }

        $uuid = $event->getRequest()->attributes->get(CartUuidService::REQUEST_ATTRIBUTE);
        if (!is_string($uuid)) {
            return;
        }

        try {
            $this->bus->dispatch(
                new SyncCartMessage($uuid, $this->cartService->getSessionItems(), new \DateTimeImmutable())
            );
        } catch (ExceptionInterface $e) {
            // Ответ уже отправлен — только фиксируем. Сессия цела,
            // синк повторится при следующем изменении корзины.
            $this->logger->warning('Не удалось поставить синк корзины в очередь', [
                'uuid' => $uuid,
                'error' => $e->getMessage(),
            ]);
        }
    }
}

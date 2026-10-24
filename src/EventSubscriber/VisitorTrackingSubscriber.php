<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Entity\Visitor;
use App\Message\TrackVisitorMessage;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class VisitorTrackingSubscriber implements EventSubscriberInterface
{
    private const string SESSION_FLAG = 'visitor_tracked';

    /** Пути, полностью исключённые из статистики: админка (/admin) и авторизация админа (/auth) */
    private const string EXCLUDED_PATHS_PATTERN = '#^/(?:admin(?:/|$)|auth/login$)#i';

    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly MessageBusInterface $bus,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            'kernel.request' => ['onKernelRequest', 256],
        ];
    }

    /**
     * @throws ExceptionInterface
     */
    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();

        if ($this->isExcludedPath($request->getPathInfo())) {
            return;
        }

        if (!$request->hasSession()) {
            return;
        }

        $session = $request->getSession();

        if ($session->get(self::SESSION_FLAG)) {
            return;
        }

        $ip = $request->getClientIp();

        if ($ip === null) {
            return;
        }

        $visitor = new Visitor($ip);
        $this->em->persist($visitor);
        $this->em->flush();

        // Флаг ставим до dispatch — исключает дубли при параллельных запросах
        $session->set(self::SESSION_FLAG, true);

        // Асинхронная геолокация через очередь
        $this->bus->dispatch(new TrackVisitorMessage((int) $visitor->getId(), $ip));
    }

    private function isExcludedPath(string $path): bool
    {
        return preg_match(self::EXCLUDED_PATHS_PATTERN, $path) === 1;
    }
}

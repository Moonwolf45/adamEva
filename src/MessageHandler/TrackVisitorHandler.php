<?php

declare(strict_types=1);

namespace App\MessageHandler;

use App\Entity\Visitor;
use App\Message\TrackVisitorMessage;
use App\Service\IpGeolocationService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class TrackVisitorHandler
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly IpGeolocationService $geolocationService,
        private readonly LoggerInterface $logger,
    ) {
    }

    /**
     * @throws OptimisticLockException
     * @throws \DateMalformedStringException
     * @throws ORMException
     */
    public function __invoke(TrackVisitorMessage $message): void
    {
        $visitor = $this->em->find(Visitor::class, $message->visitorId);

        if ($visitor === null) {
            $this->logger->warning('Visitor не найден', ['visitor_id' => $message->visitorId]);

            return;
        }

        $location = $this->geolocationService->locate($message->ip);

        $visitor->setCountry($location->country);
        $visitor->setCity($location->city);
        $visitor->setLatitude($location->latitude);
        $visitor->setLongitude($location->longitude);

        $this->em->flush();
    }
}

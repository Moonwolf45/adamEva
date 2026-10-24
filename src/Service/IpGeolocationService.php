<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\IpGeolocationCache;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

class IpGeolocationService
{
    private const CACHE_TTL_DAYS = 180;
    private const MAX_ATTEMPTS   = 5;
    private const BASE_DELAY_SEC = 60;
    private const MAX_DELAY_SEC  = 900; // 15 минут

    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly HttpIpGeolocationService $httpService,
        private readonly IpPrefixNormalizer $normalizer,
        private readonly LoggerInterface $logger,
    ) {
    }

    /**
     * @throws \DateMalformedStringException
     */
    public function locate(string $ip): IpLocation
    {
        $prefix = $this->normalizer->normalize($ip);
        $cutoff = new \DateTimeImmutable('-' . self::CACHE_TTL_DAYS . ' days');

        $cached = $this->findCached($prefix);

        // Кэш свежий — сразу отдаём, API не трогаем
        if ($cached !== null && !$cached->isStale($cutoff)) {
            return $this->toLocation($cached);
        }

        // Кэша нет или устарел — пробуем API с экспоненциальным backoff
        for ($attempt = 1; $attempt <= self::MAX_ATTEMPTS; $attempt++) {
            try {
                $location = $this->httpService->locate($ip);
                $this->saveToCache($prefix, $location);

                return $location;
            } catch (\Throwable $e) {
                if ($attempt < self::MAX_ATTEMPTS) {
                    $delay = $this->calculateDelay($attempt);

                    $this->logger->warning('ip-api недоступен, повтор через ' . $delay . 'с', [
                        'ip'       => $ip,
                        'attempt'  => $attempt,
                        'max'      => self::MAX_ATTEMPTS,
                        'error'    => $e->getMessage(),
                    ]);

                    usleep($delay * 1_000_000);
                } else {
                    // Все попытки исчерпаны — пишем лог и возвращаем старые данные
                    $this->logger->error('ip-api недоступен после ' . self::MAX_ATTEMPTS . ' попыток, использую кэш', [
                        'ip'      => $ip,
                        'cached'  => $cached?->getFetchedAt()->format('Y-m-d H:i'),
                    ]);

                    if ($cached !== null && $this->cacheHasData($cached)) {
                        return $this->toLocation($cached);
                    }

                    return IpLocation::empty();
                }
            }
        }

        return IpLocation::empty();
    }

    private function calculateDelay(int $attempt): int
    {
        $delay = self::BASE_DELAY_SEC * (2 ** ($attempt - 1));

        return min($delay, self::MAX_DELAY_SEC);
    }

    private function findCached(string $prefix): ?IpGeolocationCache
    {
        return $this->em
            ->getRepository(IpGeolocationCache::class)
            ->findOneBy(['ip_prefix' => $prefix]);
    }

    private function saveToCache(string $prefix, IpLocation $location): void
    {
        $cached = $this->findCached($prefix);

        if ($cached === null) {
            $cached = new IpGeolocationCache($prefix);
            $this->em->persist($cached);
        }

        $cached->setCountry($location->country);
        $cached->setCity($location->city);
        $cached->setLatitude($location->latitude);
        $cached->setLongitude($location->longitude);

        $this->em->flush();
    }

    private function toLocation(IpGeolocationCache $cache): IpLocation
    {
        return new IpLocation(
            $cache->getCountry(),
            $cache->getCity(),
            $cache->getLatitude(),
            $cache->getLongitude(),
        );
    }

    private function cacheHasData(IpGeolocationCache $cache): bool
    {
        return $cache->getCountry() !== null
            || $cache->getCity() !== null
            || $cache->getLatitude() !== null;
    }
}

<?php

declare(strict_types=1);

namespace App\Service;

use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class HttpIpGeolocationService
{
    private const string API_URL = 'http://ip-api.com/json/';

    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly LoggerInterface $logger
    ) {
    }

    public function locate(string $ip): IpLocation
    {
        try {
            $response = $this->httpClient->request(
                'GET',
                self::API_URL . rawurlencode($ip),
                [
                    'query'   => [
                        'fields' => 'status,country,city,lat,lon',
                        'lang'   => 'ru'
                    ],
                    'timeout' => 5,
                ]
            );

            $data = $response->toArray();
        } catch (TransportExceptionInterface $e) {
            throw new \RuntimeException(
                "Не удалось получить геолокацию для IP {$ip}: " . $e->getMessage(), 0, $e
            );
        } catch (ClientExceptionInterface $e) {
            $this->logger->error($e->getMessage(), [
                'exception' => 'ClientExceptionInterface',
            ]);
        } catch (DecodingExceptionInterface $e) {
            $this->logger->error($e->getMessage(), [
                'exception' => 'DecodingExceptionInterface',
            ]);
        } catch (RedirectionExceptionInterface $e) {
            $this->logger->error($e->getMessage(), [
                'exception' => 'RedirectionExceptionInterface',
            ]);
        } catch (ServerExceptionInterface $e) {
            $this->logger->error($e->getMessage(), [
                'exception' => 'ServerExceptionInterface',
            ]);
        }

        if (($data['status'] ?? '') !== 'ok') {
            throw new \RuntimeException(
                "ip-api вернул ошибку для {$ip}: " . ($data['message'] ?? 'unknown')
            );
        }

        return new IpLocation(
            country:   $data['country'] ?? null,
            city:      $data['city'] ?? null,
            latitude:  isset($data['lat']) ? (float) $data['lat'] : null,
            longitude: isset($data['lon']) ? (float) $data['lon'] : null,
        );
    }
}

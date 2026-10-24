<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\IpGeolocationCacheRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

#[ORM\Entity(repositoryClass: IpGeolocationCacheRepository::class)]
#[ORM\Table(name: 'ip_geolocation_cache')]
class IpGeolocationCache
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::BIGINT)]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 45, unique: true)]
    private string $ip_prefix;

    #[ORM\Column(type: 'string', length: 128, nullable: true, options: ['default' => null])]
    private ?string $country = null;

    #[ORM\Column(type: 'string', length: 128, nullable: true, options: ['default' => null])]
    private ?string $city = null;

    #[ORM\Column(type: 'float', nullable: true, options: ['default' => null])]
    private ?float $latitude = null;

    #[ORM\Column(type: 'float', nullable: true, options: ['default' => null])]
    private ?float $longitude = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    #[Gedmo\Timestampable(on: 'update')]
    private \DateTimeImmutable $fetched_at;

    public function __construct(string $ip_prefix)
    {
        $this->ip_prefix = $ip_prefix;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIpPrefix(): string
    {
        return $this->ip_prefix;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function setCountry(?string $country): void
    {
        $this->country = $country;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(?string $city): void
    {
        $this->city = $city;
    }

    public function getLatitude(): ?float
    {
        return $this->latitude;
    }

    public function setLatitude(?float $latitude): void
    {
        $this->latitude = $latitude;
    }

    public function getLongitude(): ?float
    {
        return $this->longitude;
    }

    public function setLongitude(?float $longitude): void
    {
        $this->longitude = $longitude;
    }

    public function getFetchedAt(): \DateTimeImmutable
    {
        return $this->fetched_at;
    }

    public function isStale(\DateTimeImmutable $cutoff): bool
    {
        return $this->fetched_at < $cutoff;
    }
}

<?php

namespace App\Entity;

use App\Repository\ResetPasswordRequestRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use SymfonyCasts\Bundle\ResetPassword\Model\ResetPasswordRequestInterface;
use SymfonyCasts\Bundle\ResetPassword\Model\ResetPasswordRequestTrait;

#[ORM\Entity(repositoryClass: ResetPasswordRequestRepository::class)]
class ResetPasswordRequest implements ResetPasswordRequestInterface
{
    use ResetPasswordRequestTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::BIGINT)]
    private ?int $id = null;

    #[ORM\Column(length:255)]
    private ?string $user_type = null;

    #[ORM\Column(type: "string")]
    private ?string $user_id = null;

    private object $user_object; // Свойство для хранения объекта в рантайме (не пишется в БД)

    public function __construct(object $user, \DateTimeInterface $expiresAt, string $selector, string $hashedToken)
    {
        $this->initialize($expiresAt, $selector, $hashedToken);

        $this->user_type = get_class($user);
        $this->user_id = (string) $user->getId();
        $this->user_object = $user;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): object
    {
        return $this->user_object;
    }

    public function setUserObject(object $user): void
    {
        $this->user_object = $user;
    }

    public function getUserType(): ?string
    {
        return $this->user_type;
    }

    public function getUserId(): ?string
    {
        return $this->user_id;
    }
}

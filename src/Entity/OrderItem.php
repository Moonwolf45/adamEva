<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\OrderItemsRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: OrderItemsRepository::class)]
#[ORM\Table(name: 'order_items')]
class OrderItem
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::BIGINT)]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Order::class, inversedBy: 'items')]
    #[ORM\JoinColumn(name: 'order_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private ?Order $order = null;

    #[ORM\Column(type: Types::BIGINT, nullable: true, options: ['default' => null])]
    private ?int $product_id = null;

    #[ORM\Column(length: 255)]
    private string $product_title = '';

    #[ORM\Column(length: 255, nullable: true, options: ['default' => null])]
    private ?string $product_article = null;

    #[ORM\Column(type: Types::INTEGER, options: ['default' => 1])]
    private int $quantity = 1;

    #[ORM\Column(type: Types::BIGINT, options: ['default' => 0])]
    private int $price = 0;   // коп., снапшот

    #[ORM\Column(type: Types::BIGINT, options: ['default' => 0])]
    private int $total = 0;       // unitPrice * qty

    #[ORM\Column(type: Types::JSON, nullable: true, options: ['default' => null])]
    private ?array $promotion = null;  // применённая скидка: ['type' =>, 'value' =>, 'amount' =>]

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOrder(): ?Order
    {
        return $this->order;
    }

    public function setOrder(?Order $order): static
    {
        $this->order = $order;

        return $this;
    }

    public function getProductId(): ?int
    {
        return $this->product_id;
    }

    public function setProductId(int $product_id): static
    {
        $this->product_id = $product_id;

        return $this;
    }

    public function getProductTitle(): ?string
    {
        return $this->product_title;
    }

    public function setProductTitle(string $product_title): static
    {
        $this->product_title = $product_title;

        return $this;
    }

    public function getProductArticle(): ?string
    {
        return $this->product_article;
    }

    public function setProductArticle(string $product_article): static
    {
        $this->product_article = $product_article;

        return $this;
    }

    public function getQuantity(): ?int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): static
    {
        $this->quantity = $quantity;

        return $this;
    }

    public function getPrice(): ?int
    {
        return $this->price;
    }

    public function setPrice(int $price): static
    {
        $this->price = $price;

        return $this;
    }

    public function getTotal(): ?int
    {
        return $this->total;
    }

    public function setTotal(int $total): static
    {
        $this->total = $total;

        return $this;
    }

    public function getPromotion(): array
    {
        return $this->promotion ?? [];
    }

    public function setPromotion(?array $promotion): self
    {
        $this->promotion = $promotion;
        return $this;
    }
}

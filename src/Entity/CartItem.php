<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\CartItemsRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CartItemsRepository::class)]
#[ORM\Table(name: 'cart_items')]
class CartItem
{

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::BIGINT)]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Cart::class, inversedBy: 'items')]
    #[ORM\JoinColumn(name: 'cart_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private ?Cart $cart = null;

    /**
     * Товар намеренно хранится как id, без ORM-связи:
     * при жёстком удалении товара позиция переживает как null
     * и пропускается при чтении; для заказа товар подгружается отдельно.
     */
    #[ORM\Column(type: Types::BIGINT, nullable: true, options: ['default' => null])]
    private ?int $productId = null;

    #[ORM\Column(type: Types::INTEGER, options: ['default' => 1])]
    private int $quantity = 1;

    /**
     * Снапшот цены товара в копейках на момент последней синхронизации.
     */
    #[ORM\Column(type: Types::BIGINT, options: ['default' => 0])]
    private int $price = 0;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCart(): ?Cart
    {
        return $this->cart;
    }

    public function setCart(?Cart $cart): static
    {
        $this->cart = $cart;

        return $this;
    }

    public function getProductId(): ?int
    {
        return $this->productId;
    }

    public function setProductId(?int $productId): static
    {
        $this->productId = $productId;

        return $this;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): static
    {
        $this->quantity = $quantity;

        return $this;
    }

    public function getPrice(): int
    {
        return $this->price;
    }

    public function setPrice(int $price): static
    {
        $this->price = $price;

        return $this;
    }
}

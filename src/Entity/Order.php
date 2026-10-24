<?php

declare(strict_types=1);

namespace App\Entity;

use App\Enum\OrderStatus;
use App\Enum\PaymentStatus;
use App\Repository\OrderRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

#[ORM\Entity(repositoryClass: OrderRepository::class)]
#[ORM\Table(name: 'orders')]
#[ORM\HasLifecycleCallbacks]
class Order
{
    /** Допустимые переходы жизненного цикла заказа. */
    private const array STATUS_TRANSITIONS = [
        OrderStatus::NEW->value => [OrderStatus::PROCESSING->value, OrderStatus::CANCELLED->value],
        OrderStatus::PROCESSING->value => [OrderStatus::SHIPPED->value, OrderStatus::CANCELLED->value],
        OrderStatus::SHIPPED->value => [OrderStatus::DELIVERED->value],
        OrderStatus::DELIVERED->value => [],
        OrderStatus::CANCELLED->value => [],
    ];

    /** Допустимые переходы статуса оплаты. */
    private const array PAYMENT_STATUS_TRANSITIONS = [
        PaymentStatus::PENDING->value => [PaymentStatus::PROCESSING->value, PaymentStatus::PAID->value, PaymentStatus::FAILED->value],
        PaymentStatus::PROCESSING->value => [PaymentStatus::PAID->value, PaymentStatus::FAILED->value],
        PaymentStatus::PAID->value => [PaymentStatus::REFUNDED->value],
        PaymentStatus::FAILED->value => [PaymentStatus::PENDING->value],
        PaymentStatus::REFUNDED->value => [],
    ];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::BIGINT)]
    private ?int $id = null;

    /** Человекочитаемый номер, например "20250112-00042". */
    #[ORM\Column(length: 32, unique: true)]
    private string $number = '';

    /** UUID корзины на момент заказа — защита от повторного оформления (двойной клик). */
    #[ORM\Column(length: 45, nullable: true, index: true)]
    private ?string $cart_uuid = null;

    #[ORM\ManyToOne(targetEntity: Delivery::class)]
    #[ORM\JoinColumn(name: 'delivery_id', referencedColumnName: 'id', onDelete: 'RESTRICT')]
    private ?Delivery $delivery = null;

    #[ORM\Column(length: 255)]
    private string $delivery_name = '';

    #[ORM\Column(type: Types::BIGINT, options: ['default' => 0])]
    private int $delivery_price = 0;

    #[ORM\ManyToOne(targetEntity: Payment::class)]
    #[ORM\JoinColumn(name: 'payment_id', referencedColumnName: 'id', onDelete: 'RESTRICT')]
    private ?Payment $payment = null;

    #[ORM\Column(length: 255)]
    private string $payment_name = '';

    #[ORM\Column(length: 32, options: ['default' => PaymentStatus::PENDING->value])]
    private string $payment_status = PaymentStatus::PENDING->value;

    #[ORM\Column(length: 255, nullable: true, options: ['default' => null])]
    private ?string $payment_ref = null;

    #[ORM\Column(type: Types::BIGINT, options: ['default' => 0])]
    private int $items_total = 0;

    #[ORM\Column(type: Types::BIGINT, options: ['default' => 0])]
    private int $discount_total = 0;

    #[ORM\Column(type: Types::BIGINT, options: ['default' => 0])]
    private int $delivery_total = 0;

    #[ORM\Column(type: Types::BIGINT, options: ['default' => 0])]
    private int $total = 0;

    #[ORM\Column(length: 32, options: ['default' => OrderStatus::NEW->value])]
    private string $status = OrderStatus::NEW->value;

    #[ORM\Column(length: 1000, nullable: true, options: ['default' => null])]
    private ?string $admin_comment = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true, options: ['default' => null])]
    private ?\DateTimeImmutable $paid_at = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true, options: ['default' => null])]
    private ?\DateTimeImmutable $shipped_at = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true, options: ['default' => null])]
    private ?\DateTimeImmutable $completed_at = null;

    #[ORM\OneToMany(targetEntity: OrderItem::class, mappedBy: 'order', cascade: ['persist'], orphanRemoval: true)]
    private Collection $items;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    #[Gedmo\Timestampable(on: 'create')]
    private ?\DateTimeImmutable $created_at = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, index: true)]
    #[Gedmo\Timestampable(on: 'update')]
    private ?\DateTimeImmutable $updated_at = null;

    public function __construct()
    {
        $this->items = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNumber(): ?string
    {
        return $this->number;
    }

    public function setNumber(string $number): static
    {
        $this->number = $number;

        return $this;
    }

    public function getCartUuid(): ?string
    {
        return $this->cart_uuid;
    }

    public function setCartUuid(string $cart_uuid): static
    {
        $this->cart_uuid = $cart_uuid;

        return $this;
    }

    public function getDelivery(): Delivery
    {
        return $this->delivery;
    }

    public function setDelivery(Delivery $delivery): static
    {
        $this->delivery = $delivery;

        return $this;
    }

    public function getDeliveryName(): ?string
    {
        return $this->delivery_name;
    }

    public function setDeliveryName(string $delivery_name): static
    {
        $this->delivery_name = $delivery_name;

        return $this;
    }

    public function getDeliveryPrice(): ?int
    {
        return $this->delivery_price;
    }

    public function setDeliveryPrice(int $delivery_price): static
    {
        $this->delivery_price = $delivery_price;

        return $this;
    }

    public function getPayment(): Payment
    {
        return $this->payment;
    }

    public function setPayment(Payment $payment): static
    {
        $this->payment = $payment;

        return $this;
    }

    public function getPaymentName(): ?string
    {
        return $this->payment_name;
    }

    public function setPaymentName(string $payment_name): static
    {
        $this->payment_name = $payment_name;

        return $this;
    }

    public function getPaymentStatus(): ?string
    {
        return $this->payment_status;
    }

    public function setPaymentStatus(string $payment_status): static
    {
        $this->payment_status = $payment_status;

        return $this;
    }

    public function getPaymentRef(): ?string
    {
        return $this->payment_ref;
    }

    public function setPaymentRef(string $payment_ref): static
    {
        $this->payment_ref = $payment_ref;

        return $this;
    }

    public function getItemsTotal(): ?int
    {
        return $this->items_total;
    }

    public function setItemsTotal(int $items_total): static
    {
        $this->items_total = $items_total;

        return $this;
    }

    public function getDiscountTotal(): ?int
    {
        return $this->discount_total;
    }

    public function setDiscountTotal(int $discount_total): static
    {
        $this->discount_total = $discount_total;

        return $this;
    }

    public function getDeliveryTotal(): ?int
    {
        return $this->delivery_total;
    }

    public function setDeliveryTotal(int $delivery_total): static
    {
        $this->delivery_total = $delivery_total;

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

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getAdminComment(): ?string
    {
        return $this->admin_comment;
    }

    public function setAdminComment(string $admin_comment): static
    {
        $this->admin_comment = $admin_comment;

        return $this;
    }

    public function getPaidAt(): ?\DateTimeImmutable
    {
        return $this->paid_at;
    }

    public function setPaidAt(?\DateTimeImmutable $paid_at): static
    {
        $this->paid_at = $paid_at;

        return $this;
    }

    public function getShippedAt(): ?\DateTimeImmutable
    {
        return $this->shipped_at;
    }

    public function setShippedAt(?\DateTimeImmutable $shipped_at): static
    {
        $this->shipped_at = $shipped_at;

        return $this;
    }

    public function getCompletedAt(): ?\DateTimeImmutable
    {
        return $this->completed_at;
    }

    public function setCompletedAt(?\DateTimeImmutable $completed_at): static
    {
        $this->completed_at = $completed_at;

        return $this;
    }

    /**
     * @return Collection<int, CartItem>
     */
    public function getItems(): Collection
    {
        return $this->items;
    }

    public function addItem(OrderItem $item): static
    {
        if (!$this->items->contains($item)) {
            $this->items->add($item);
            $item->setOrder($this);
        }

        return $this;
    }

    public function removeItem(OrderItem $item): static
    {
        if ($this->items->removeElement($item)) {
            if ($item->getOrder() === $this) {
                $item->setOrder(null);
            }
        }

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->created_at;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updated_at;
    }
}

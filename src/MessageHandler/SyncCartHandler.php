<?php

declare(strict_types=1);

namespace App\MessageHandler;

use App\Entity\Cart;
use App\Entity\CartItem;
use App\Message\SyncCartMessage;
use App\Repository\CartRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class SyncCartHandler
{
    /**
     * Снапшот старше этого возраста для несуществующей корзины не применяем:
     * либо корзина уже закрыта (оформлен заказ), либо воркер сильно отстал.
     */
    private const int MAX_MESSAGE_AGE = 30;

    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly CartRepository $cartRepository,
    ) {
    }

    public function __invoke(SyncCartMessage $message): void
    {
        $cart = $this->cartRepository->findOneByUuid($message->uuid);

        if ($cart === null) {
            if ($message->items === []) {
                return;
            }

            if ((time() - $message->syncedAt->getTimestamp()) > self::MAX_MESSAGE_AGE) {
                return;
            }

            $cart = new Cart();
            $cart->setUuid($message->uuid);
            $this->em->persist($cart);
        }

        // Более свежий синк уже выполнен (в т.ч. при повторной доставке сообщения)
        if ($cart->getSyncedAt() !== null && $cart->getSyncedAt() >= $message->syncedAt) {
            return;
        }

        // Корзина опустела — запись не нужна. При следующем добавлении
        // синк создаст её заново.
        if ($message->items === []) {
            $this->em->remove($cart);
            $this->em->flush();

            return;
        }

        $this->syncItems($cart, $message->items);
        $cart->setSyncedAt($message->syncedAt);

        $this->em->flush();
    }

    /**
     * Полная замена состава: лишнее удаляем, остальное создаём/обновляем.
     *
     * @param array<int, array{product_id: int, quantity: int, price: int}> $items
     */
    private function syncItems(Cart $cart, array $items): void
    {
        $wanted = [];
        foreach ($items as $item) {
            $wanted[$item['product_id']] = $item;
        }

        foreach ($cart->getItems() as $existing) {
            $productId = $existing->getProductId();

            if ($productId === null || !isset($wanted[$productId])) {
                $cart->removeItem($existing);
                continue;
            }

            unset($wanted[$productId]);
        }

        foreach ($wanted as $productId => $data) {
            $item = $cart->findItem($productId);

            if ($item === null) {
                $item = new CartItem();
                $item->setProductId($productId);
                $cart->addItem($item);
            }

            $item->setQuantity($data['quantity']);
            $item->setPrice($data['price']);
        }
    }
}

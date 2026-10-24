<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Product;
use App\Repository\CartRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * Корзина посетителя.
 *
 * Сессия — источник правды в рантайме: чтения/записи без БД.
 * БД — холодное зеркало, обновляется асинхронно через очередь
 * (CartSyncSubscriber / SyncCartHandler) и используется только
 * для восстановления корзины при утере сессии.
 */
final class CartService
{
    public const string SESSION_ITEMS_KEY = 'cart_items';
    public const string SESSION_HYDRATED_KEY = 'cart_hydrated';

    /** Максимум штук одной позиции. */
    private const int MAX_QTY = 99;

    /** Максимум позиций в корзине. */
    private const int MAX_POSITIONS = 50;

    /**
     * Менялась ли корзина в текущем запросе.
     * Живёт только в памяти (scope = request); subscriber читает в kernel.terminate.
     */
    private bool $dirty = false;

    public function __construct(
        private readonly RequestStack $requestStack,
        private readonly CartRepository $cartRepository,
        private readonly CartUuidService $cartUuidService,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * Позиции корзины, ключ — id товара.
     *
     * @return array<int, array{product_id: int, quantity: int, price: int}>
     */
    public function getItems(): array
    {
        $session = $this->getSession();
        if ($session === null) {
            return [];
        }

        return $this->getOrCreateItems($session);
    }

    public function addItem(Product $product, int $quantity = 1): bool
    {
        if ($product->getId() === null || !$product->isStatus() || $product->getDeletedAt() !== null) {
            return false;
        }

        $session = $this->getSession();
        if ($session === null) {
            return false;
        }

        $items = $this->getOrCreateItems($session);
        $productId = (int) $product->getId();

        if (!isset($items[$productId]) && count($items) >= self::MAX_POSITIONS) {
            return false;
        }

        $items[$productId] = [
            'product_id' => $productId,
            'quantity' => min(($items[$productId]['quantity'] ?? 0) + max(1, $quantity), self::MAX_QTY),
            'price' => $product->getPrice(),
        ];

        $this->saveItems($session, $items);

        return true;
    }

    public function setQuantity(int $productId, int $quantity): bool
    {
        if ($productId < 1) {
            return false;
        }

        if ($quantity <= 0) {
            return $this->removeItem($productId);
        }

        $session = $this->getSession();
        if ($session === null) {
            return false;
        }

        $items = $this->getOrCreateItems($session);
        if (!isset($items[$productId])) {
            return false;
        }

        $items[$productId]['quantity'] = min(max(1, $quantity), self::MAX_QTY);

        $this->saveItems($session, $items);

        return true;
    }

    public function removeItem(int $productId): bool
    {
        $session = $this->getSession();
        if ($session === null) {
            return false;
        }

        $items = $this->getOrCreateItems($session);
        if (!isset($items[$productId])) {
            return false;
        }

        unset($items[$productId]);

        $this->saveItems($session, $items);

        return true;
    }

    /**
     * Полная очистка: сессия + синхронное удаление из БД.
     * Вызывается при завершении оформления заказа (после его коммита).
     */
    public function clear(): void
    {
        $this->dirty = false;

        $session = $this->getSession();
        if ($session !== null) {
            $session->remove(self::SESSION_ITEMS_KEY);
            $session->set(self::SESSION_HYDRATED_KEY, true);
        }

        $uuid = $this->getUuid();
        if ($uuid === null) {
            return;
        }

        $cart = $this->cartRepository->findOneByUuid($uuid);
        if ($cart === null) {
            return;
        }

        $this->entityManager->remove($cart);
        $this->entityManager->flush();
    }

    /**
     * Состояние сессии без гидратации из БД (для отправки в очередь).
     *
     * @return array<int, array{product_id: int, quantity: int, price: int}>
     */
    public function getSessionItems(): array
    {
        $items = $this->getSession()?->get(self::SESSION_ITEMS_KEY);

        return is_array($items) ? $items : [];
    }

    public function isDirty(): bool
    {
        return $this->dirty;
    }

    /**
     * Общее количество штук (для бейджа в шапке).
     */
    public function getCount(): int
    {
        $count = 0;
        foreach ($this->getItems() as $item) {
            $count += $item['quantity'];
        }

        return $count;
    }

    /**
     * Сумма по снапшоту цен в копейках.
     * Для заказа сумму пересчитывать по актуальным ценам и акциям.
     */
    public function getTotal(): int
    {
        $total = 0;
        foreach ($this->getItems() as $item) {
            $total += $item['quantity'] * $item['price'];
        }

        return $total;
    }

    /**
     * @return array<int, array{product_id: int, quantity: int, price: int}>
     */
    private function getOrCreateItems(SessionInterface $session): array
    {
        $items = $session->get(self::SESSION_ITEMS_KEY);
        if (is_array($items)) {
            return $items;
        }

        $items = $this->hydrateFromDatabase($session);
        $session->set(self::SESSION_ITEMS_KEY, $items);

        return $items;
    }

    /**
     * Разовая (на жизнь сессии) загрузка корзины из БД,
     * если состояние сессии утеряно, но кука uuid жива.
     *
     * @return array<int, array{product_id: int, quantity: int, price: int}>
     */
    private function hydrateFromDatabase(SessionInterface $session): array
    {
        if ((bool) $session->get(self::SESSION_HYDRATED_KEY, false)) {
            return [];
        }

        $session->set(self::SESSION_HYDRATED_KEY, true);

        $uuid = $this->getUuid();
        if ($uuid === null) {
            return [];
        }

        // uuid только что сгенерирован — в БД для него ничего быть не могло
        $request = $this->requestStack->getMainRequest();
        if ($request !== null && $this->cartUuidService->isNewUuid($request)) {
            return [];
        }

        $cart = $this->cartRepository->findOneByUuid($uuid);
        if ($cart === null) {
            return [];
        }

        $items = [];
        foreach ($cart->getItems() as $item) {
            $productId = $item->getProductId();
            if ($productId === null) {
                continue;
            }

            $items[$productId] = [
                'product_id' => $productId,
                'quantity' => $item->getQuantity(),
                'price' => $item->getPrice(),
            ];
        }

        return $items;
    }

    /**
     * @param array<int, array{product_id: int, quantity: int, price: int}> $items
     */
    private function saveItems(SessionInterface $session, array $items): void
    {
        $session->set(self::SESSION_ITEMS_KEY, $items);
        $this->dirty = true;
    }

    private function getUuid(): ?string
    {
        $uuid = $this->requestStack->getMainRequest()?->attributes->get(CartUuidService::REQUEST_ATTRIBUTE);

        return is_string($uuid) ? $uuid : null;
    }

    private function getSession(): ?SessionInterface
    {
        $request = $this->requestStack->getMainRequest();

        if ($request === null || !$request->hasSession()) {
            return null;
        }

        return $request->getSession();
    }
}

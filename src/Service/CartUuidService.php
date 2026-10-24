<?php

declare(strict_types=1);

namespace App\Service;

use Random\RandomException;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class CartUuidService
{
    public const COOKIE_NAME = 'cartUuid';
    public const REQUEST_ATTRIBUTE = 'cart_uuid';

    private const NEW_FLAG = 'cart_uuid_new';
    private const LIFETIME = 365 * 24 * 3600; // 1 год
    private const UUID_V4_PATTERN = '/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i';

    /**
     * Был ли uuid сгенерирован в текущем запросе (куки у клиента не было).
     * Для свежего uuid в БД ничего быть не могло — позволяет пропустить гидратацию корзины.
     */
    public function isNewUuid(Request $request): bool
    {
        return $request->attributes->getBoolean(self::NEW_FLAG);
    }

    /**
     * Возвращает uuid посетителя. Если куки нет — генерирует новый
     * и помечает запрос, чтобы кукa была добавлена в ответ.
     *
     * @throws RandomException
     */
    public function getOrCreate(Request $request): string
    {
        $existing = $request->cookies->get(self::COOKIE_NAME);

        if (is_string($existing) && preg_match(self::UUID_V4_PATTERN, $existing) === 1) {
            $request->attributes->set(self::REQUEST_ATTRIBUTE, $existing);

            return $existing;
        }

        $uuid = $this->generateUuidV4();

        $request->attributes->set(self::REQUEST_ATTRIBUTE, $uuid);
        $request->attributes->set(self::NEW_FLAG, true);

        $request->cookies->set(self::COOKIE_NAME, $uuid);

        return $uuid;
    }

    /**
     * Добавляет куку в ответ, если новый uuid был сгенерирован в этом запросе.
     */
    public function attachCookieIfNew(Request $request, Response $response): void
    {
        if (!$request->attributes->getBoolean(self::NEW_FLAG)) {
            return;
        }

        $cookie = Cookie::create(
            self::COOKIE_NAME,
            (string) $request->attributes->get(self::REQUEST_ATTRIBUTE),
            time() + self::LIFETIME,
            '/',
            null,
            $request->isSecure(), // secure на HTTPS, не ломает локальную HTTP-разработку
            true,                 // httpOnly
            false,
            'Lax',
        );

        $response->headers->setCookie($cookie);
    }

    /**
     * @throws RandomException
     */
    private function generateUuidV4(): string
    {
        $bytes = random_bytes(16);
        $bytes[6] = chr((ord($bytes[6]) & 0x0F) | 0x40); // version 4
        $bytes[8] = chr((ord($bytes[8]) & 0x3F) | 0x80); // variant 10

        return $bytes
            |> bin2hex(...)
            |> (fn($x) => str_split($x, 4))
            |> (fn($x) => vsprintf('%s%s-%s-%s-%s-%s%s%s', $x));
    }
}

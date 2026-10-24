<?php

declare(strict_types=1);

namespace App\Service;

class ProductImagePathNormalizer
{
    private const UPLOADS_PREFIX = '/uploads';

    public function normalize(?string $path): ?string
    {
        if ($path === null) {
            return null;
        }

        $path = trim($path);

        if ($path === '') {
            return $path;
        }

        // Не трогаем полные URL: https://..., http://..., //cdn.example.com/...
        if (
            preg_match('#^(?:[a-z][a-z0-9+.\-]*:)?//#i', $path) === 1
            || preg_match('#^data:#i', $path) === 1
        ) {
            return $path;
        }

        // Приводим Windows-слэш к обычному
        $path = str_replace('\\', '/', $path);

        // Убираем лишние повторяющиеся слэши
        $path = preg_replace('#/{2,}#', '/', $path) ?? $path;

        // Оставляем только один начальный слэш
        $path = preg_replace('#^/+#', '/', $path) ?? $path;

        $relativeUploadsPrefix = ltrim(self::UPLOADS_PREFIX, '/');

        // Уже точное совпадение: /uploads или uploads
        if ($path === self::UPLOADS_PREFIX || $path === $relativeUploadsPrefix) {
            return self::UPLOADS_PREFIX;
        }

        // Уже нормальный путь: /uploads/...
        if (str_starts_with($path, self::UPLOADS_PREFIX . '/')) {
            return $path;
        }

        // Путь начинается с uploads/ без начального слэша: uploads/...
        if (str_starts_with($path, $relativeUploadsPrefix . '/')) {
            return self::UPLOADS_PREFIX . substr($path, strlen($relativeUploadsPrefix . '/'));
        }

        // Есть начальный слэш, но нет uploads: /photo.jpg -> /uploads/photo.jpg
        if (str_starts_with($path, '/')) {
            return self::UPLOADS_PREFIX . $path;
        }

        // Нет начального слэша: photo.jpg -> /uploads/photo.jpg
        return self::UPLOADS_PREFIX . '/' . $path;
    }
}

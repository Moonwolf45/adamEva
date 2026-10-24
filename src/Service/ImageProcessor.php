<?php

declare(strict_types=1);

namespace App\Service;

use App\Enum\ImageType;
use FilesystemIterator;
use Intervention\Image\Interfaces\ImageManagerInterface;
use InvalidArgumentException;
use Random\RandomException;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RuntimeException;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Централизованная обработка и хранение загруженных изображений.
 *
 * Файлы сохраняются в public/uploads/{тип}/{id_сущности}/
 * Ужимаются с сохранением пропорций до MAX_WIDTH x MAX_HEIGHT.
 */
class ImageProcessor
{
    public const int MAX_WIDTH = 1920;
    public const int MAX_HEIGHT = 1080;

    private const int QUALITY = 90;
    private const int DIR_MODE = 0o775;
    private const string UPLOADS_DIR = 'uploads';

    /**
     * Допустимые MIME-типы => расширение, под которым сохраняем файл.
     *
     * @var array<string, string>
     */
    private const array MIME_TYPES = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    ];

    public function __construct(
        private readonly ImageManagerInterface $imageManager,
        private readonly string $publicDirectory,
    ) {
    }

    /**
     * Обрабатывает изображение и сохраняет его на диске.
     *
     * @param File $file загруженный файл (обычно UploadedFile из формы)
     * @param ImageType $type тип изображения (директория)
     * @param int $entityId id сущности, к которой относится файл
     * @param string|null $baseName базовое имя файла без расширения (опционально)
     *
     * @return string путь относительно public/, например "uploads/products/1/a1b2c3d4.jpg"
     *
     * @throws RandomException
     */
    public function upload(File $file, ImageType $type, int $entityId, ?string $baseName = null): string
    {
        if ($file instanceof UploadedFile && !$file->isValid()) {
            throw new RuntimeException('Загруженный файл невалиден.');
        }

        $extension = $this->resolveExtension($file);
        $directory = $this->entityDirectory($type, $entityId);
        $target = $directory . DIRECTORY_SEPARATOR . $this->buildFileName($baseName, $extension);

        $image = $this->imageManager->decodePath($file->getPathname());

        // Уменьшаем только если изображение больше 1920x1080, пропорции сохраняются.
        $image->scaleDown(self::MAX_WIDTH, self::MAX_HEIGHT);

        // quality применяется к jpeg/webp, для png Intervention его игнорирует.
        $image->save($target, quality: self::QUALITY);

        return $this->toPublicPath($target);
    }

    /**
     * Удаляет один файл по пути относительно public/.
     */
    public function delete(string $publicPath): void
    {
        $absolute = $this->toAbsolutePath($publicPath);

        if (is_file($absolute)) {
            @unlink($absolute);
        }
    }

    /**
     * Удаляет все изображения сущности (её каталог целиком).
     */
    public function deleteEntityFiles(ImageType $type, int $entityId): void
    {
        $directory = $this->entityDirectory($type, $entityId);

        if (!is_dir($directory)) {
            return;
        }

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($directory, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST,
        );

        foreach ($iterator as $item) {
            $item->isDir() ? rmdir($item->getPathname()) : @unlink($item->getPathname());
        }

        @rmdir($directory);
    }

    private function resolveExtension(File $file): string
    {
        $mimeType = $file->getMimeType();
        $extension = self::MIME_TYPES[$mimeType] ?? null;

        if ($extension === null) {
            throw new RuntimeException(sprintf(
                'Тип изображения "%s" не поддерживается.',
                $mimeType ?? 'unknown',
            ));
        }

        return $extension;
    }

    private function entityDirectory(ImageType $type, int $entityId): string
    {
        $directory = $this->uploadsDirectory()
            . DIRECTORY_SEPARATOR . $type->value
            . DIRECTORY_SEPARATOR . $entityId;

        if (!is_dir($directory) && !mkdir($directory, self::DIR_MODE, true) && !is_dir($directory)) {
            throw new RuntimeException(sprintf('Не удалось создать каталог "%s".', $directory));
        }

        return $directory;
    }

    /**
     * @throws RandomException
     */
    private function buildFileName(?string $baseName, string $extension): string
    {
        $base = $baseName !== null && trim($baseName) !== ''
            ? (string) preg_replace('/[^\w\-]/u', '_', $baseName)
            : bin2hex(random_bytes(8));

        return $base . '.' . $extension;
    }

    private function uploadsDirectory(): string
    {
        return $this->publicDirectory . DIRECTORY_SEPARATOR . self::UPLOADS_DIR;
    }

    /**
     * Абсолютный путь => путь относительно public/ со слешами.
     */
    private function toPublicPath(string $absolutePath): string
    {
        $prefix = $this->publicDirectory . DIRECTORY_SEPARATOR;

        $relative = str_starts_with($absolutePath, $prefix)
            ? substr($absolutePath, strlen($prefix))
            : $absolutePath;

        return str_replace(DIRECTORY_SEPARATOR, '/', $relative);
    }

    private function toAbsolutePath(string $publicPath): string
    {
        $normalized = str_replace('\\', '/', trim($publicPath, '/'));

        if ($normalized === '' || str_contains($normalized, '..')) {
            throw new InvalidArgumentException('Некорректный путь к файлу.');
        }

        return $this->publicDirectory . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $normalized);
    }
}

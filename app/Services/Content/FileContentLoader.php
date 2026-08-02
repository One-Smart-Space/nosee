<?php

declare(strict_types=1);

namespace App\Services\Content;

use App\Exceptions\InvalidContentFileException;
use App\Exceptions\InvalidContentPathException;
use DirectoryIterator;
use Throwable;

final class FileContentLoader
{
    private readonly string $basePath;

    private readonly ContentPathValidator $pathValidator;

    public function __construct(?string $basePath = null, ?ContentPathValidator $pathValidator = null)
    {
        $resolvedBasePath = realpath(
            $basePath ?? dirname(__DIR__, 3).DIRECTORY_SEPARATOR.'content',
        );

        if ($resolvedBasePath === false || ! is_dir($resolvedBasePath)) {
            throw InvalidContentPathException::forBasePath();
        }

        $this->basePath = $resolvedBasePath;
        $this->pathValidator = $pathValidator ?? new ContentPathValidator;
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function loadCollection(string $collection): array
    {
        $records = [];

        foreach ($this->collectionFiles($collection) as $path) {
            $slug = pathinfo($path, PATHINFO_FILENAME);
            $this->pathValidator->assertValidSlug($slug);
            $records[$slug] = $this->loadFile($path);
        }

        return $records;
    }

    /**
     * @return list<string>
     */
    public function collectionFiles(string $collection): array
    {
        $directory = $this->collectionDirectory($collection);

        if ($directory === null) {
            return [];
        }

        $files = [];

        foreach (new DirectoryIterator($directory) as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $files[] = $collection.'/'.$file->getFilename();
            }
        }

        sort($files, SORT_STRING);

        return $files;
    }

    /**
     * @return array<string, mixed>
     */
    public function loadFile(string $path): array
    {
        $this->pathValidator->assertValidFile($path);

        $absolutePath = $this->basePath.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $path);

        if (! is_file($absolutePath)) {
            throw InvalidContentFileException::missing($path);
        }

        return $this->requireFile(
            $path,
            $this->resolvedFilePath($absolutePath, $path),
        );
    }

    /**
     * @return array<string, mixed>|null
     */
    public function load(string $collection, string $slug): ?array
    {
        $path = $this->contentFile($collection, $slug);

        return $path === null
            ? null
            : $this->requireFile($collection.'/'.$slug.'.php', $path);
    }

    public function exists(string $collection, string $slug): bool
    {
        return $this->contentFile($collection, $slug) !== null;
    }

    private function collectionDirectory(string $collection): ?string
    {
        $this->pathValidator->assertValidCollection($collection);

        $path = $this->basePath.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $collection);

        if (! is_dir($path)) {
            return null;
        }

        $resolvedPath = realpath($path);

        if ($resolvedPath === false || ! $this->isInsideBasePath($resolvedPath)) {
            throw InvalidContentPathException::forCollection($collection);
        }

        return $resolvedPath;
    }

    private function contentFile(string $collection, string $slug): ?string
    {
        $this->pathValidator->assertValidSlug($slug);
        $directory = $this->collectionDirectory($collection);

        if ($directory === null) {
            return null;
        }

        $path = $directory.DIRECTORY_SEPARATOR.$slug.'.php';

        if (! is_file($path)) {
            return null;
        }

        return $this->resolvedFilePath($path, $collection.'/'.$slug.'.php');
    }

    private function resolvedFilePath(string $path, string $relativePath): string
    {
        $resolvedPath = realpath($path);

        if ($resolvedPath === false || ! $this->isInsideBasePath($resolvedPath)) {
            throw InvalidContentPathException::forFile($relativePath);
        }

        return $resolvedPath;
    }

    private function isInsideBasePath(string $path): bool
    {
        return $path === $this->basePath
            || str_starts_with($path, $this->basePath.DIRECTORY_SEPARATOR);
    }

    /**
     * @return array<string, mixed>
     */
    private function requireFile(string $relativePath, string $path): array
    {
        try {
            $record = (static fn (): mixed => require $path)();
        } catch (Throwable $exception) {
            throw InvalidContentFileException::unreadable($relativePath, $exception);
        }

        if (! is_array($record)) {
            throw InvalidContentFileException::mustReturnArray($relativePath);
        }

        return $record;
    }
}

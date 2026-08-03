<?php

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;

final class InvalidContentPathException extends RuntimeException
{
    public static function forBasePath(): self
    {
        return new self('The configured content base directory is invalid or missing.');
    }

    public static function forCollection(string $collection): self
    {
        return new self("Invalid content collection path [{$collection}].");
    }

    public static function forSlug(string $slug): self
    {
        return new self("Invalid content slug [{$slug}].");
    }

    public static function forFile(string $path): self
    {
        return new self("Invalid content file path [{$path}].");
    }
}

<?php

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;
use Throwable;

final class InvalidContentFileException extends RuntimeException
{
    public static function missing(string $path): self
    {
        return new self("Content file [{$path}] does not exist.");
    }

    public static function mustReturnArray(string $path): self
    {
        return new self("Content file [{$path}] must return an array.");
    }

    public static function unreadable(string $path, Throwable $previous): self
    {
        return new self("Content file [{$path}] could not be loaded.", previous: $previous);
    }
}

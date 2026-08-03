<?php

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;

final class ContentNotFoundException extends RuntimeException
{
    public function __construct(
        public readonly string $collection,
        public readonly string $slug,
    ) {
        parent::__construct("Content record [{$slug}] was not found in collection [{$collection}].");
    }
}

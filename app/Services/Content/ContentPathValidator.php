<?php

declare(strict_types=1);

namespace App\Services\Content;

use App\Exceptions\InvalidContentPathException;

final class ContentPathValidator
{
    private const SEGMENT_PATTERN = '/\A[a-z0-9]+(?:-[a-z0-9]+)*\z/D';

    public function assertValidSlug(string $slug): void
    {
        if (! $this->isValidSegment($slug)) {
            throw InvalidContentPathException::forSlug($slug);
        }
    }

    public function assertValidCollection(string $collection): void
    {
        $segments = explode('/', $collection);

        if ($collection === '' || str_contains($collection, '\\')) {
            throw InvalidContentPathException::forCollection($collection);
        }

        foreach ($segments as $segment) {
            if (! $this->isValidSegment($segment)) {
                throw InvalidContentPathException::forCollection($collection);
            }
        }
    }

    public function assertValidFile(string $path): void
    {
        if (
            ! str_ends_with($path, '.php')
            || str_contains($path, '\\')
            || str_starts_with($path, '/')
        ) {
            throw InvalidContentPathException::forFile($path);
        }

        $segments = explode('/', substr($path, 0, -4));

        foreach ($segments as $segment) {
            if (! $this->isValidSegment($segment)) {
                throw InvalidContentPathException::forFile($path);
            }
        }
    }

    private function isValidSegment(string $segment): bool
    {
        return preg_match(self::SEGMENT_PATTERN, $segment) === 1;
    }
}

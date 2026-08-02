<?php

declare(strict_types=1);

namespace App\Contracts\Content;

use App\Exceptions\ContentNotFoundException;

interface ContentRepository
{
    /**
     * @return list<array<string, mixed>>
     */
    public function all(): array;

    /**
     * @return array<string, mixed>|null
     */
    public function find(string $slug): ?array;

    public function exists(string $slug): bool;

    /**
     * @return array<string, mixed>
     *
     * @throws ContentNotFoundException
     */
    public function findOrFail(string $slug): array;
}

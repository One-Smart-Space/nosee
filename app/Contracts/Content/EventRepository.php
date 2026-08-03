<?php

declare(strict_types=1);

namespace App\Contracts\Content;

interface EventRepository extends ContentRepository
{
    /**
     * @return list<array<string, mixed>>
     */
    public function featuredUpcoming(int $limit = 3): array;
}

<?php

declare(strict_types=1);

namespace App\Contracts\Content;

interface NewsRepository extends ContentRepository
{
    /**
     * @return list<array<string, mixed>>
     */
    public function homepageTrending(int $limit = 4): array;
}

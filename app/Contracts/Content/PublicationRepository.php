<?php

declare(strict_types=1);

namespace App\Contracts\Content;

interface PublicationRepository extends ContentRepository
{
    /** @return list<array<string, mixed>> */
    public function featured(int $limit = 3): array;
}

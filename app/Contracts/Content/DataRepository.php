<?php

declare(strict_types=1);

namespace App\Contracts\Content;

interface DataRepository extends ContentRepository
{
    /** @return list<array<string, mixed>> */
    public function homepageMonitoring(): array;
}

<?php

declare(strict_types=1);

namespace App\Repositories\Content;

use App\Contracts\Content\DataRepository;

final class FileDataRepository extends AbstractFileContentRepository implements DataRepository
{
    public function homepageMonitoring(): array
    {
        // Preserve the loader's file order while excluding non-homepage records.
        return array_values(array_filter(
            $this->all(),
            static fn (array $record): bool => ($record['homepage_monitoring'] ?? false) === true,
        ));
    }

    protected function collection(): string
    {
        return 'data/items';
    }
}

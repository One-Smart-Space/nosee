<?php

declare(strict_types=1);

namespace App\Repositories\Content;

use App\Contracts\Content\PublicationRepository;

final class FilePublicationRepository extends AbstractFileContentRepository implements PublicationRepository
{
    public function featured(int $limit = 3): array
    {
        if ($limit <= 0) {
            return [];
        }

        // Filter strictly before sorting newest-first with a stable slug tie-breaker.
        $records = array_values(array_filter(
            $this->all(),
            static fn (array $record): bool => ($record['featured'] ?? false) === true,
        ));

        usort(
            $records,
            static fn (array $left, array $right): int =>
                strcmp($right['publication_date'], $left['publication_date'])
                ?: strcmp($left['slug'], $right['slug']),
        );

        return array_slice($records, 0, $limit);
    }

    protected function collection(): string
    {
        return 'publications';
    }
}

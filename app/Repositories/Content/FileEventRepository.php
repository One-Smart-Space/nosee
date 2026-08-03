<?php

declare(strict_types=1);

namespace App\Repositories\Content;

use App\Contracts\Content\EventRepository;
use DateTimeImmutable;

final class FileEventRepository extends AbstractFileContentRepository implements EventRepository
{
    public function featuredUpcoming(int $limit = 3): array
    {
        if ($limit <= 0) {
            return [];
        }

        $today = (new DateTimeImmutable('today'))->format('Y-m-d');
        $records = array_values(array_filter(
            $this->all(),
            static fn (array $record): bool => ($record['featured'] ?? false) === true
                && ($record['end_date'] ?? '') >= $today,
        ));

        // Start date drives the listing; slugs keep equal dates deterministic.
        usort($records, static fn (array $left, array $right): int =>
            strcmp((string) $left['start_date'], (string) $right['start_date'])
            ?: strcmp((string) $left['slug'], (string) $right['slug'])
        );

        return array_slice($records, 0, $limit);
    }

    protected function collection(): string
    {
        return 'events';
    }
}

<?php

declare(strict_types=1);

namespace App\Repositories\Content;

use App\Contracts\Content\NewsRepository;

final class FileNewsRepository extends AbstractFileContentRepository implements NewsRepository
{
    public function homepageTrending(int $limit = 4): array
    {
        if ($limit <= 0) {
            return [];
        }

        $records = $this->all();

        // Keep the featured story first, then resolve equal dates by slug for stable output.
        usort($records, static function (array $left, array $right): int {
            $featuredOrder = (int) (($right['featured'] ?? false) === true)
                <=> (int) (($left['featured'] ?? false) === true);

            return $featuredOrder
                ?: strcmp((string) $right['published_at'], (string) $left['published_at'])
                ?: strcmp((string) $left['slug'], (string) $right['slug']);
        });

        return array_slice($records, 0, $limit);
    }

    protected function collection(): string
    {
        return 'news';
    }
}

<?php

declare(strict_types=1);

namespace App\Repositories\Content;

use App\Contracts\Content\ResearchRepository;

final class FileResearchRepository extends AbstractFileContentRepository implements ResearchRepository
{
    protected function collection(): string
    {
        return 'research/areas';
    }
}

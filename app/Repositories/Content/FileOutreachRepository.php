<?php

declare(strict_types=1);

namespace App\Repositories\Content;

use App\Contracts\Content\OutreachRepository;

final class FileOutreachRepository extends AbstractFileContentRepository implements OutreachRepository
{
    protected function collection(): string
    {
        return 'outreach';
    }
}

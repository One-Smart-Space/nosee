<?php

declare(strict_types=1);

namespace App\Repositories\Content;

use App\Contracts\Content\EventRepository;

final class FileEventRepository extends AbstractFileContentRepository implements EventRepository
{
    protected function collection(): string
    {
        return 'events';
    }
}

<?php

declare(strict_types=1);

namespace App\Repositories\Content;

use App\Contracts\Content\MeetingRepository;

final class FileMeetingRepository extends AbstractFileContentRepository implements MeetingRepository
{
    protected function collection(): string
    {
        return 'meetings';
    }
}

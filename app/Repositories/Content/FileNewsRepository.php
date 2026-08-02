<?php

declare(strict_types=1);

namespace App\Repositories\Content;

use App\Contracts\Content\NewsRepository;

final class FileNewsRepository extends AbstractFileContentRepository implements NewsRepository
{
    protected function collection(): string
    {
        return 'news';
    }
}

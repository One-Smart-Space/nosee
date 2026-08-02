<?php

declare(strict_types=1);

namespace App\Repositories\Content;

use App\Contracts\Content\PublicationRepository;

final class FilePublicationRepository extends AbstractFileContentRepository implements PublicationRepository
{
    protected function collection(): string
    {
        return 'publications';
    }
}

<?php

declare(strict_types=1);

namespace App\Repositories\Content;

use App\Contracts\Content\ProductRepository;

final class FileProductRepository extends AbstractFileContentRepository implements ProductRepository
{
    protected function collection(): string
    {
        return 'products/items';
    }
}

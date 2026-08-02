<?php

declare(strict_types=1);

namespace App\Services\Content;

use App\Contracts\Content\DataRepository;

final class HomepageContentService
{
    public function __construct(
        private readonly FileContentLoader $loader,
        private readonly DataRepository $data,
    ) {
    }

    /** @return array<string, mixed> */
    public function get(): array
    {
        // Combine editorial homepage content with repository-selected catalogue records.
        return [
            ...$this->loader->loadFile('homepage.php'),
            'monitoring' => $this->data->homepageMonitoring(),
        ];
    }
}

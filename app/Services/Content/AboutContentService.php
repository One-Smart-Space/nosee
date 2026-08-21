<?php

declare(strict_types=1);

namespace App\Services\Content;

final class AboutContentService
{
    public function __construct(
        private readonly FileContentLoader $loader,
    ) {}

    /** @return array<string, mixed> */
    public function get(): array
    {
        return $this->loader->loadFile('about.php');
    }
}

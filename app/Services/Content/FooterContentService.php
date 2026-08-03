<?php

declare(strict_types=1);

namespace App\Services\Content;

use App\Exceptions\InvalidContentFileException;

final class FooterContentService
{
    public function __construct(
        private readonly FileContentLoader $loader,
        private readonly ContentValidationService $validation,
    ) {
    }

    /** @return array<string, mixed> */
    public function get(): array
    {
        $site = $this->loader->loadFile('site.php');
        $errors = $this->validation->validateRecord(ContentSchemaRegistry::SITE, $site);

        if ($errors !== []) {
            throw new InvalidContentFileException(
                'Content file [site.php] failed validation: '.implode(' ', $errors),
            );
        }

        return $site['footer'];
    }
}

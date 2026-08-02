<?php

declare(strict_types=1);

namespace App\Services\Content;

use App\Exceptions\InvalidContentFileException;
use App\Exceptions\InvalidContentPathException;
use Illuminate\Contracts\Validation\Factory;

final class ContentValidationService
{
    public function __construct(
        private readonly FileContentLoader $loader,
        private readonly ContentSchemaRegistry $schemas,
        private readonly Factory $validator,
        private readonly ContentPathValidator $pathValidator,
    ) {
    }

    /**
     * @return array{files: int, errors: array<string, list<string>>}
     */
    public function validateAll(): array
    {
        $files = 0;
        $errors = [];
        $homepageMonitoringRecords = 0;

        foreach ($this->schemas->singletons() as $path => $schema) {
            $files++;
            $this->validateFile($path, $schema, null, $errors);
        }

        foreach ($this->schemas->collections() as $collection => $schema) {
            try {
                $collectionFiles = $this->loader->collectionFiles($collection);
            } catch (InvalidContentPathException $exception) {
                $errors[$collection][] = $exception->getMessage();

                continue;
            }

            foreach ($collectionFiles as $path) {
                $files++;
                $content = $this->validateFile(
                    $path,
                    $schema,
                    pathinfo($path, PATHINFO_FILENAME),
                    $errors,
                );

                if ($collection === 'data/items' && ($content['homepage_monitoring'] ?? null) === true) {
                    $homepageMonitoringRecords++;
                }
            }
        }

        // Keep the homepage selection deterministic without coupling it to file names.
        if ($homepageMonitoringRecords !== 3) {
            $errors['data/items'][] = 'Exactly three data records must enable homepage monitoring.';
        }

        return ['files' => $files, 'errors' => $errors];
    }

    /**
     * @param  array<string, mixed>  $content
     * @return list<string>
     */
    public function validateRecord(string $schema, array $content, ?string $expectedSlug = null): array
    {
        $validation = $this->validator->make($content, $this->schemas->rules($schema));
        $errors = $validation->errors()->all();
        $slug = $content['slug'] ?? null;

        if (is_string($slug)) {
            try {
                $this->pathValidator->assertValidSlug($slug);
            } catch (InvalidContentPathException $exception) {
                $errors[] = 'slug: '.$exception->getMessage();
            }

            if ($expectedSlug !== null && $slug !== $expectedSlug) {
                $errors[] = "slug: The value must match filename [{$expectedSlug}.php].";
            }
        }

        return array_values($errors);
    }

    /**
     * @param  array<string, list<string>>  $errors
     * @return array<string, mixed>|null
     */
    private function validateFile(
        string $path,
        string $schema,
        ?string $expectedSlug,
        array &$errors,
    ): ?array {
        try {
            $content = $this->loader->loadFile($path);
        } catch (InvalidContentFileException|InvalidContentPathException $exception) {
            $errors[$path][] = $exception->getMessage();

            return null;
        }

        $messages = $this->validateRecord($schema, $content, $expectedSlug);

        if ($messages !== []) {
            $errors[$path] = $messages;
        }

        return $content;
    }
}

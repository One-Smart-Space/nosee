<?php

declare(strict_types=1);

namespace App\Repositories\Content;

use App\Contracts\Content\ContentRepository;
use App\Exceptions\ContentNotFoundException;
use App\Services\Content\FileContentLoader;

abstract class AbstractFileContentRepository implements ContentRepository
{
    public function __construct(private readonly FileContentLoader $loader)
    {
    }

    public function all(): array
    {
        $records = [];

        foreach ($this->loader->loadCollection($this->collection()) as $slug => $record) {
            $records[] = $this->withSlug($record, $slug);
        }

        return $records;
    }

    public function find(string $slug): ?array
    {
        $record = $this->loader->load($this->collection(), $slug);

        return $record === null ? null : $this->withSlug($record, $slug);
    }

    public function exists(string $slug): bool
    {
        return $this->loader->exists($this->collection(), $slug);
    }

    public function findOrFail(string $slug): array
    {
        return $this->find($slug)
            ?? throw new ContentNotFoundException($this->collection(), $slug);
    }

    abstract protected function collection(): string;

    /**
     * @param  array<string, mixed>  $record
     * @return array<string, mixed>
     */
    private function withSlug(array $record, string $slug): array
    {
        return array_key_exists('slug', $record)
            ? $record
            : ['slug' => $slug, ...$record];
    }
}

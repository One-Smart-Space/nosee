<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Content;

use App\Contracts\Content\PublicationRepository;
use App\Services\Content\ContentSchemaRegistry;
use App\Services\Content\ContentValidationService;
use Tests\TestCase;

class PublicationContentTest extends TestCase
{
    public function test_ten_valid_records_cover_every_research_area_with_three_featured(): void
    {
        $records = $this->app->make(PublicationRepository::class)->all();
        $featured = array_values(array_filter(
            $records,
            static fn (array $record): bool => $record['featured'] === true,
        ));
        $researchAreas = array_values(array_unique(array_column($records, 'research_area')));
        sort($researchAreas);

        $this->assertCount(10, $records);
        $this->assertCount(3, $featured);
        $this->assertSame([
            'atmosphere-and-air-quality',
            'climate-science',
            'earth-and-space-informatics',
            'energy-resources-and-environment',
            'space-weather',
        ], $researchAreas);

        foreach ($records as $record) {
            $this->assertFileExists(base_path("content/publications/{$record['slug']}.php"));
            $this->assertStringStartsNotWith('https://doi.org/', $record['doi']);
        }
    }

    public function test_schema_rejects_unsupported_types_and_non_boolean_featured_values(): void
    {
        $record = $this->record();
        $record['type'] = 'unsupported';
        $this->assertErrorContains($this->validate($record), 'type');

        $record = $this->record();
        $record['publication_date'] = '2026-02-31';
        $this->assertErrorContains($this->validate($record), 'publication date');

        $record = $this->record();
        $record['featured'] = 1;
        $this->assertErrorContains($this->validate($record), 'featured');
    }

    public function test_schema_rejects_invalid_doi_and_research_area_references(): void
    {
        $record = $this->record();
        $record['doi'] = 'https://doi.org/10.5555/example';
        $this->assertErrorContains($this->validate($record), 'doi');

        $record = $this->record();
        $record['research_area'] = 'not-a-research-area';
        $this->assertErrorContains($this->validate($record), 'research area');
    }

    public function test_featured_query_returns_the_three_newest_records(): void
    {
        $records = $this->app->make(PublicationRepository::class)->featured();

        $this->assertCount(3, $records);
        $this->assertSame([
            'low-latitude-geomagnetic-storm-responses',
            'urban-aerosol-optical-depth-patterns',
            'satellite-soil-moisture-drought-signals',
        ], array_column($records, 'slug'));
    }

    /** @return array<string, mixed> */
    private function record(): array
    {
        return require base_path('content/publications/low-latitude-geomagnetic-storm-responses.php');
    }

    /** @return list<string> */
    private function validate(array $record): array
    {
        return $this->app->make(ContentValidationService::class)->validateRecord(
            ContentSchemaRegistry::PUBLICATION,
            $record,
            $record['slug'],
        );
    }

    /** @param list<string> $errors */
    private function assertErrorContains(array $errors, string $expected): void
    {
        $this->assertNotEmpty(array_filter(
            $errors,
            static fn (string $error): bool => str_contains(strtolower($error), strtolower($expected)),
        ));
    }
}

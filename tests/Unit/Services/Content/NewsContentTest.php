<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Content;

use App\Contracts\Content\NewsRepository;
use App\Services\Content\ContentSchemaRegistry;
use App\Services\Content\ContentValidationService;
use DateTimeImmutable;
use Tests\TestCase;

class NewsContentTest extends TestCase
{
    private const CATEGORIES = [
        'organisation-news',
        'research-news',
        'member-achievement',
        'partnership',
        'funding-opportunity',
        'scientific-development',
        'outreach-report',
    ];

    public function test_nine_valid_records_use_local_images_with_exactly_one_featured(): void
    {
        $records = $this->app->make(NewsRepository::class)->all();
        $featured = array_filter(
            $records,
            static fn (array $record): bool => $record['featured'] === true,
        );

        $this->assertCount(9, $records);
        $this->assertCount(1, $featured);

        foreach ($records as $record) {
            $date = DateTimeImmutable::createFromFormat('Y-m-d\TH:i:sP', $record['published_at']);

            $this->assertFileExists(base_path("content/news/{$record['slug']}.php"));
            $this->assertContains($record['category'], self::CATEGORIES);
            $this->assertNotFalse($date);
            $this->assertSame($record['published_at'], $date->format('Y-m-d\TH:i:sP'));
            $this->assertIsBool($record['featured']);
            $this->assertStringStartsWith('/media/trending/', $record['image']);
            $this->assertFileExists(public_path(ltrim($record['image'], '/')));
            $this->assertNotSame('', trim($record['image_alt']));
            $this->assertSame([], $this->validate($record));
        }
    }

    public function test_schema_rejects_invalid_category_date_image_alt_and_boolean_values(): void
    {
        $record = $this->record();
        $record['category'] = 'unsupported-category';
        $this->assertErrorContains($this->validate($record), 'category');

        $record = $this->record();
        $record['published_at'] = '2026-02-31T09:00:00+00:00';
        $this->assertErrorContains($this->validate($record), 'published at');

        $record = $this->record();
        $record['image'] = '/media/trending/missing.png';
        $this->assertErrorContains($this->validate($record), 'existing public file');

        $record = $this->record();
        $record['image_alt'] = '   ';
        $this->assertErrorContains($this->validate($record), 'image alt');

        $record = $this->record();
        $record['featured'] = 1;
        $this->assertErrorContains($this->validate($record), 'featured');
    }

    public function test_homepage_query_returns_featured_then_three_newest_unique_records(): void
    {
        $records = $this->app->make(NewsRepository::class)->homepageTrending();

        $this->assertCount(4, $records);
        $this->assertCount(4, array_unique(array_column($records, 'slug')));
        $this->assertSame([
            'solar-storm-monitoring-exercise',
            'space-station-instrument-calibration',
            'air-quality-smoke-transport-study',
            'regional-earth-observation-partnership',
        ], array_column($records, 'slug'));
    }

    /** @return array<string, mixed> */
    private function record(): array
    {
        return require base_path('content/news/solar-storm-monitoring-exercise.php');
    }

    /** @return list<string> */
    private function validate(array $record): array
    {
        return $this->app->make(ContentValidationService::class)->validateRecord(
            ContentSchemaRegistry::NEWS,
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

<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Content;

use App\Repositories\Content\FileDataRepository;
use App\Services\Content\ContentSchemaRegistry;
use App\Services\Content\ContentValidationService;
use App\Services\Content\FileContentLoader;
use Tests\TestCase;

class MonitoringDashboardContentTest extends TestCase
{
    public function test_three_external_monitoring_records_use_the_matching_local_images(): void
    {
        $records = (new FileDataRepository(new FileContentLoader))->all();
        $monitoring = array_values(array_filter(
            $records,
            static fn (array $record): bool => $record['homepage_monitoring'] === true,
        ));

        $this->assertCount(3, $monitoring);
        $this->assertSame([
            'average-f-region-observations',
            'range-error-from-tec',
            'tec-difference-from-30-day-median',
        ], array_column($monitoring, 'slug'));

        foreach ($monitoring as $record) {
            $this->assertSame('external', $record['ownership']);
            $this->assertSame('National Weather Services', $record['provider']);
            $this->assertSame('https://www.spaceweather.gov/products/glotec', $record['source_url']);
            $this->assertNotSame('', trim($record['image_alt']));
            $this->assertFileExists(public_path(ltrim($record['image'], '/')));
        }
    }

    public function test_monitoring_image_alt_and_boolean_fields_are_validated(): void
    {
        $record = $this->record();
        unset($record['image']);
        $this->assertErrorContains($this->validate($record), 'image');

        $record = $this->record();
        $record['image_alt'] = '   ';
        $this->assertErrorContains($this->validate($record), 'image alt');

        $record = $this->record();
        $record['homepage_monitoring'] = 'true';
        $this->assertErrorContains($this->validate($record), 'homepage monitoring');
    }

    public function test_missing_monitoring_images_are_rejected(): void
    {
        $record = $this->record();
        $record['image'] = '/media/monitoringDashboards/missing.png';

        $this->assertErrorContains($this->validate($record), 'existing public file');
    }

    /**
     * @return array<string, mixed>
     */
    private function record(): array
    {
        return require base_path('content/data/items/range-error-from-tec.php');
    }

    /**
     * @param  array<string, mixed>  $record
     * @return list<string>
     */
    private function validate(array $record): array
    {
        return $this->app->make(ContentValidationService::class)->validateRecord(
            ContentSchemaRegistry::DATA_ITEM,
            $record,
            'range-error-from-tec',
        );
    }

    /**
     * @param  list<string>  $errors
     */
    private function assertErrorContains(array $errors, string $expected): void
    {
        $matchingErrors = array_filter(
            $errors,
            static fn (string $error): bool => str_contains(strtolower($error), strtolower($expected)),
        );

        $this->assertNotEmpty($matchingErrors);
    }
}

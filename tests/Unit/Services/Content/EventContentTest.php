<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Content;

use App\Contracts\Content\EventRepository;
use App\Services\Content\ContentSchemaRegistry;
use App\Services\Content\ContentValidationService;
use DateTimeImmutable;
use Tests\TestCase;

class EventContentTest extends TestCase
{
    private const TYPES = [
        'conference',
        'workshop',
        'seminar',
        'lecture',
        'training',
        'meeting',
        'outreach',
        'deadline',
    ];

    public function test_nine_valid_records_include_three_featured_upcoming_events(): void
    {
        $repository = $this->app->make(EventRepository::class);
        $records = $repository->all();
        $featured = array_values(array_filter(
            $records,
            static fn (array $record): bool => $record['featured'] === true,
        ));
        $today = (new DateTimeImmutable('today'))->format('Y-m-d');

        $this->assertCount(9, $records);
        $this->assertCount(3, $featured);
        $this->assertCount(3, $repository->featuredUpcoming());

        foreach ($records as $record) {
            $startDate = DateTimeImmutable::createFromFormat('!Y-m-d', $record['start_date']);
            $endDate = DateTimeImmutable::createFromFormat('!Y-m-d', $record['end_date']);

            $this->assertFileExists(base_path("content/events/{$record['slug']}.php"));
            $this->assertContains($record['type'], self::TYPES);
            $this->assertNotFalse($startDate);
            $this->assertNotFalse($endDate);
            $this->assertGreaterThanOrEqual($record['start_date'], $record['end_date']);
            $this->assertIsBool($record['featured']);
            $this->assertTrue($record['registration_url'] === null || filter_var($record['registration_url'], FILTER_VALIDATE_URL) !== false);
            $this->assertSame([], $this->validate($record));
        }

        foreach ($featured as $record) {
            $this->assertGreaterThanOrEqual($today, $record['end_date']);
        }
    }

    public function test_schema_rejects_invalid_type_dates_url_and_boolean_values(): void
    {
        $record = $this->record();
        $record['type'] = 'unsupported';
        $this->assertErrorContains($this->validate($record), 'type');

        $record = $this->record();
        $record['start_date'] = '2027-02-31';
        $this->assertErrorContains($this->validate($record), 'start date');

        $record = $this->record();
        $record['end_date'] = '2027-03-01';
        $this->assertErrorContains($this->validate($record), 'end date');

        $record = $this->record();
        $record['registration_url'] = 'not-a-url';
        $this->assertErrorContains($this->validate($record), 'registration url');

        $record = $this->record();
        $record['featured'] = 1;
        $this->assertErrorContains($this->validate($record), 'featured');
    }

    public function test_featured_query_returns_records_in_start_date_order(): void
    {
        $records = $this->app->make(EventRepository::class)->featuredUpcoming();

        $this->assertSame([
            'regional-space-weather-conference',
            'earth-observation-data-workshop',
            'atmospheric-measurement-seminar',
        ], array_column($records, 'slug'));
    }

    /** @return array<string, mixed> */
    private function record(): array
    {
        return require base_path('content/events/regional-space-weather-conference.php');
    }

    /** @return list<string> */
    private function validate(array $record): array
    {
        return $this->app->make(ContentValidationService::class)->validateRecord(
            ContentSchemaRegistry::EVENT,
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

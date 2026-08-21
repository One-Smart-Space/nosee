<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Content;

use App\Contracts\Content\EventRepository;
use App\Services\Content\ContentSchemaRegistry;
use App\Services\Content\ContentValidationService;
use Tests\TestCase;

class EventContentTest extends TestCase
{
    private const TYPES = [
        'conference',
        'workshop',
        'lecture',
        'training',
        'meeting',
        'outreach',
    ];

    public function test_nine_migrated_records_cover_the_event_domain_with_three_featured(): void
    {
        $records = $this->app->make(EventRepository::class)->all();
        $featured = array_filter(
            $records,
            static fn (array $record): bool => $record['featured'] === true,
        );

        $this->assertCount(9, $records);
        $this->assertCount(3, $featured);
        $this->assertSame(self::TYPES, array_values(array_intersect(self::TYPES, array_unique(array_column($records, 'type')))));
        $locationTypes = array_values(array_unique(array_column(array_column($records, 'location'), 'type')));
        sort($locationTypes);

        $this->assertSame(['hybrid', 'online', 'physical'], $locationTypes);
        $this->assertNotEmpty(array_filter($records, static fn (array $record): bool => $record['application_deadline'] !== null));
        $this->assertNotEmpty(array_filter($records, static fn (array $record): bool => $record['type'] !== 'meeting'
            && $record['schedule']['mode'] === 'multi_day'
            && $record['schedule']['itinerary'] !== []
        ));

        foreach ($records as $record) {
            $this->assertFileExists(base_path("content/events/{$record['slug']}.php"));
            $this->assertIsBool($record['featured']);
            $this->assertArrayNotHasKey('is_meeting', $record);
            $this->assertSame([], $this->validate($record));
        }
    }

    public function test_schema_supports_exactly_the_six_event_types(): void
    {
        foreach (['conference', 'workshop', 'lecture', 'training', 'outreach'] as $type) {
            $record = $this->record('satellite-climate-lecture');
            $record['type'] = $type;

            $this->assertSame([], $this->validate($record));
        }

        $this->assertSame([], $this->validate($this->record('icelli2026')));

        foreach (['deadline', 'seminar', 'webinar'] as $type) {
            $record = $this->record('satellite-climate-lecture');
            $record['type'] = $type;

            $this->assertErrorContains($this->validate($record), 'type');
        }
    }

    public function test_meeting_rules_require_an_https_site_but_allow_microsite_owned_fields_to_be_empty(): void
    {
        $meeting = $this->record('icelli2026');

        $this->assertSame('', $meeting['organiser']);
        $this->assertSame('', $meeting['body']);
        $this->assertSame([], $this->validate($meeting));

        $meeting['meeting_site_url'] = null;
        $this->assertErrorContains($this->validate($meeting), 'meeting site url');

        $meeting = $this->record('icelli2026');
        $meeting['meeting_site_url'] = 'http://icelli.nosee.org';
        $this->assertErrorContains($this->validate($meeting), 'https');

        $meeting = $this->record('icelli2026');
        $meeting['end_date'] = null;
        $this->assertErrorContains($this->validate($meeting), 'later than the start date');

        $meeting = $this->record('icelli2026');
        $meeting['schedule'] = [
            'mode' => 'single_day',
            'start_time' => null,
            'end_time' => null,
            'itinerary' => [],
        ];
        $meeting['end_date'] = null;
        $this->assertSame([], $this->validate($meeting));
    }

    public function test_regular_event_rules_require_detail_content_and_valid_urls(): void
    {
        $record = $this->record();
        $record['meeting_site_url'] = 'https://meetings.example.org/event';
        $this->assertErrorContains($this->validate($record), 'regular events');

        $record = $this->record();
        $record['organiser'] = '';
        $this->assertErrorContains($this->validate($record), 'organiser');

        $record = $this->record();
        $record['body'] = '';
        $this->assertErrorContains($this->validate($record), 'body');

        $record = $this->record();
        $record['registration_url'] = 'not-a-url';
        $this->assertErrorContains($this->validate($record), 'registration url');

        $record = $this->record();
        $record['resources'] = [['label' => 'Broken resource', 'url' => 'not-a-url']];
        $this->assertErrorContains($this->validate($record), 'resources.0.url');

        $record = $this->record();
        $record['image'] = '/media/events/development.jpg';
        $record['image_alt'] = null;
        $this->assertErrorContains($this->validate($record), 'image alt');
    }

    public function test_single_day_regular_events_require_canonical_forward_moving_times(): void
    {
        $record = $this->record('satellite-climate-lecture');
        $record['schedule']['start_time'] = null;
        $this->assertErrorContains($this->validate($record), 'start time');

        $record = $this->record('satellite-climate-lecture');
        $record['end_date'] = $record['start_date'];
        $this->assertErrorContains($this->validate($record), 'end date');

        $record = $this->record('satellite-climate-lecture');
        $record['schedule']['end_time'] = $record['schedule']['start_time'];
        $this->assertErrorContains($this->validate($record), 'end time');

        $record = $this->record('satellite-climate-lecture');
        $record['schedule']['start_time'] = '9:00';
        $this->assertErrorContains($this->validate($record), 'start time');

        $record = $this->record('satellite-climate-lecture');
        $record['schedule']['itinerary'] = [[
            'date' => $record['start_date'],
            'start_time' => '11:00',
            'end_time' => null,
            'title' => null,
            'description' => null,
        ]];
        $this->assertErrorContains($this->validate($record), 'empty itinerary');
    }

    public function test_multi_day_regular_events_require_a_valid_range_and_complete_ordered_itinerary(): void
    {
        $record = $this->record();
        $record['end_date'] = null;
        $this->assertErrorContains($this->validate($record), 'later than the start date');

        $record = $this->record();
        $record['end_date'] = $record['start_date'];
        $this->assertErrorContains($this->validate($record), 'later than the start date');

        $record = $this->record();
        $record['schedule']['start_time'] = '09:00';
        $this->assertErrorContains($this->validate($record), 'top-level times');

        $record = $this->record();
        array_pop($record['schedule']['itinerary']);
        $this->assertErrorContains($this->validate($record), 'exactly cover');

        $record = $this->record();
        $record['schedule']['itinerary'] = array_reverse($record['schedule']['itinerary']);
        $this->assertErrorContains($this->validate($record), 'ordered');

        $record = $this->record();
        $record['schedule']['itinerary'][0]['end_time'] = '08:00';
        $this->assertErrorContains($this->validate($record), 'end time');
    }

    public function test_structured_locations_enforce_physical_online_and_hybrid_requirements(): void
    {
        $physical = $this->record('regional-space-weather-conference');
        $physical['location']['venue'] = null;
        $this->assertErrorContains($this->validate($physical), 'location.venue');

        $online = $this->record('satellite-climate-lecture');
        $online['location']['platform'] = null;
        $this->assertErrorContains($this->validate($online), 'location.platform');

        $hybrid = $this->record();
        $hybrid['location']['city'] = null;
        $hybrid['location']['platform'] = null;
        $errors = $this->validate($hybrid);
        $this->assertErrorContains($errors, 'location.city');
        $this->assertErrorContains($errors, 'location.platform');
    }

    public function test_application_deadline_requires_iso_date_time_but_may_be_expired(): void
    {
        $record = $this->record('community-sky-observation-day');
        $record['application_deadline'] = '2026-09-20';
        $this->assertErrorContains($this->validate($record), 'application deadline');

        $record['application_deadline'] = '2020-01-01T09:30:00+01:00';
        $this->assertSame([], $this->validate($record));
    }

    public function test_event_type_presentation_is_central_and_uses_the_requested_palettes(): void
    {
        $this->assertSame([
            'meeting' => ['tag_classes' => 'bg-brand/10 text-brand', 'duration_classes' => 'bg-brand text-inverse'],
            'conference' => ['tag_classes' => 'bg-purple-100 text-purple-800', 'duration_classes' => 'bg-surface text-primary'],
            'workshop' => ['tag_classes' => 'bg-orange-100 text-orange-800', 'duration_classes' => 'bg-surface text-primary'],
            'lecture' => ['tag_classes' => 'bg-blue-100 text-blue-800', 'duration_classes' => 'bg-surface text-primary'],
            'training' => ['tag_classes' => 'bg-sky-100 text-sky-800', 'duration_classes' => 'bg-surface text-primary'],
            'outreach' => ['tag_classes' => 'bg-rose-100 text-rose-800', 'duration_classes' => 'bg-surface text-primary'],
        ], config('event-types'));
    }

    public function test_featured_query_uses_nullable_end_dates_and_filename_must_match_slug(): void
    {
        $records = $this->app->make(EventRepository::class)->featuredUpcoming();

        $this->assertSame([
            'regional-space-weather-conference',
            'earth-observation-data-workshop',
            'atmospheric-measurement-seminar',
        ], array_column($records, 'slug'));

        $this->assertErrorContains(
            $this->validate($this->record(), 'different-filename'),
            'must match filename',
        );
    }

    /** @return array<string, mixed> */
    private function record(string $slug = 'earth-observation-data-workshop'): array
    {
        return require base_path("content/events/{$slug}.php");
    }

    /** @return list<string> */
    private function validate(array $record, ?string $expectedSlug = null): array
    {
        return $this->app->make(ContentValidationService::class)->validateRecord(
            ContentSchemaRegistry::EVENT,
            $record,
            $expectedSlug ?? $record['slug'],
        );
    }

    /** @param list<string> $errors */
    private function assertErrorContains(array $errors, string $expected): void
    {
        $this->assertNotEmpty(array_filter(
            $errors,
            static fn (string $error): bool => str_contains(strtolower($error), strtolower($expected)),
        ), "Failed asserting that validation errors contain [{$expected}].");
    }
}

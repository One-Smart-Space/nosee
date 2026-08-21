<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Events;

use App\Contracts\Content\EventRepository;
use App\Services\Events\EventListingService;
use Carbon\CarbonImmutable;
use Tests\TestCase;

class EventListingServiceTest extends TestCase
{
    public function test_event_state_and_featured_selection_are_derived_at_the_event_timezone(): void
    {
        $now = CarbonImmutable::parse('2026-08-31 10:00:00', 'UTC');
        $ongoingMulti = $this->event('ongoing-multi', [
            'start_date' => '2026-08-30',
            'end_date' => '2026-09-01',
            'schedule' => ['mode' => 'multi_day', 'start_time' => null, 'end_time' => null],
        ]);
        $ongoingMeeting = $this->event('ongoing-meeting', [
            'type' => 'meeting',
            'start_date' => '2026-08-31',
            'schedule' => ['start_time' => null, 'end_time' => null],
            'meeting_site_url' => 'https://meeting.example.org',
        ]);
        $ongoingOpen = $this->event('ongoing-open', [
            'start_date' => '2026-08-31',
            'schedule' => ['start_time' => '08:00', 'end_time' => null],
        ]);
        $ongoingTimed = $this->event('ongoing-timed', [
            'start_date' => '2026-08-31',
            'schedule' => ['start_time' => '10:00', 'end_time' => '12:00'],
        ]);
        $pastTimed = $this->event('past-timed', [
            'start_date' => '2026-08-31',
            'schedule' => ['start_time' => '09:00', 'end_time' => '10:00'],
        ]);
        $upcomingRegular = $this->event('upcoming-regular', [
            'featured' => true,
            'start_date' => '2026-08-31',
            'schedule' => ['start_time' => '12:00', 'end_time' => '13:00'],
        ]);
        $featuredMeeting = $this->event('featured-meeting', [
            'type' => 'meeting',
            'featured' => true,
            'start_date' => '2026-09-02',
            'end_date' => '2026-09-04',
            'schedule' => ['mode' => 'multi_day', 'start_time' => null, 'end_time' => null],
            'meeting_site_url' => 'https://featured.example.org',
        ]);
        $laterFeaturedMeeting = $this->event('later-featured-meeting', [
            'type' => 'meeting',
            'featured' => true,
            'start_date' => '2026-09-03',
            'meeting_site_url' => 'https://later.example.org',
        ]);

        $result = $this->service([
            $laterFeaturedMeeting,
            $pastTimed,
            $ongoingTimed,
            $featuredMeeting,
            $ongoingMulti,
            $upcomingRegular,
            $ongoingOpen,
            $ongoingMeeting,
        ])->build(now: $now);

        $this->assertSame('featured-meeting', $result['featured']['slug']);
        $this->assertSame(
            ['ongoing-multi', 'ongoing-meeting', 'ongoing-open', 'ongoing-timed'],
            array_column($result['ongoing'], 'slug'),
        );
        $this->assertSame(
            ['upcoming-regular', 'featured-meeting', 'later-featured-meeting'],
            array_column($result['upcoming'], 'slug'),
        );
        $this->assertNotContains('past-timed', array_column([...$result['ongoing'], ...$result['upcoming']], 'slug'));

        $fallback = $this->service([
            $upcomingRegular,
            [...$featuredMeeting, 'featured' => false],
        ])->build(now: $now);

        $this->assertSame('upcoming-regular', $fallback['featured']['slug']);
    }

    public function test_search_filters_only_upcoming_events_and_groups_them_by_start_month_and_day(): void
    {
        $now = CarbonImmutable::parse('2026-08-30 12:00:00', 'Africa/Lagos');
        $crossMonth = $this->event('cross-month', [
            'title' => 'Cross-month workshop',
            'start_date' => '2026-08-31',
            'end_date' => '2026-09-02',
            'schedule' => ['mode' => 'multi_day', 'start_time' => null, 'end_time' => null],
        ]);
        $augustLagos = $this->event('august-lagos', [
            'start_date' => '2026-08-31',
            'schedule' => ['start_time' => '09:00', 'end_time' => '11:00'],
            'location' => ['city' => 'Lagos'],
        ]);
        $augustOnline = $this->event('august-online', [
            'start_date' => '2026-08-31',
            'schedule' => ['start_time' => '11:00', 'end_time' => '12:00'],
            'location' => [
                'type' => 'online',
                'venue' => null,
                'address' => null,
                'city' => null,
                'country' => null,
                'platform' => 'Microsoft Teams',
            ],
        ]);
        $september = $this->event('september-event', [
            'start_date' => '2026-09-05',
        ]);
        $ongoingLagos = $this->event('ongoing-lagos', [
            'start_date' => '2026-08-29',
            'end_date' => '2026-08-30',
            'schedule' => ['mode' => 'multi_day', 'start_time' => null, 'end_time' => null],
            'location' => ['city' => 'Lagos'],
        ]);
        $events = [$september, $augustOnline, $ongoingLagos, $augustLagos, $crossMonth];

        $unfiltered = $this->service($events)->build(now: $now);

        $this->assertSame(['2026-08', '2026-09'], array_column($unfiltered['upcoming_groups'], 'key'));
        $this->assertSame('August 2026', $unfiltered['upcoming_groups'][0]['label']);
        $this->assertSame('31 AUG', $unfiltered['upcoming_groups'][0]['days'][0]['label']);
        $this->assertSame(
            ['cross-month', 'august-lagos', 'august-online'],
            array_column($unfiltered['upcoming_groups'][0]['days'][0]['events'], 'slug'),
        );
        $this->assertSame(['september-event'], array_column($unfiltered['upcoming_groups'][1]['days'][0]['events'], 'slug'));

        $filtered = $this->service($events)->build('  LaGoS  ', $now);

        $this->assertSame('LaGoS', $filtered['query']);
        $this->assertSame(['august-lagos'], array_column($filtered['upcoming'], 'slug'));
        $this->assertSame(['ongoing-lagos'], array_column($filtered['ongoing'], 'slug'));
        $this->assertSame('cross-month', $filtered['featured']['slug']);
        $this->assertSame(['2026-08'], array_column($filtered['upcoming_groups'], 'key'));
        $this->assertSame(['august-lagos'], array_column($filtered['upcoming_groups'][0]['days'][0]['events'], 'slug'));
    }

    public function test_deadline_location_date_time_and_destination_presentation_is_prepared_for_views(): void
    {
        $now = CarbonImmutable::parse('2026-08-31 12:00:00', 'Africa/Lagos');
        $futureDeadline = $this->event('future-deadline', [
            'start_date' => '2026-09-02',
            'schedule' => ['start_time' => '15:00', 'end_time' => '16:30'],
            'application_deadline' => '2026-09-01T17:00:00+01:00',
            'location' => [
                'type' => 'hybrid',
                'venue' => 'Development Centre',
                'address' => 'University Road',
                'city' => 'Lagos',
                'country' => 'Nigeria',
                'platform' => 'Zoom',
            ],
        ]);
        $closed = $this->event('closed-event', [
            'start_date' => '2026-09-03',
            'application_deadline' => '2026-08-30T17:00:00+01:00',
        ]);
        $meeting = $this->event('meeting-event', [
            'type' => 'meeting',
            'featured' => true,
            'start_date' => '2026-09-04',
            'end_date' => '2026-09-06',
            'schedule' => ['mode' => 'multi_day', 'start_time' => null, 'end_time' => null],
            'meeting_site_url' => 'https://meeting.example.org',
            'location' => [
                'type' => 'online',
                'venue' => null,
                'address' => null,
                'city' => null,
                'country' => null,
                'platform' => 'Zoom',
            ],
        ]);

        $events = array_column(
            $this->service([$meeting, $closed, $futureDeadline])->build(now: $now)['upcoming'],
            null,
            'slug',
        );

        $this->assertSame('/events/future-deadline', $events['future-deadline']['destination']);
        $this->assertFalse($events['future-deadline']['external']);
        $this->assertSame('3:00 PM', $events['future-deadline']['presentation']['start_time']);
        $this->assertSame('4:30 PM', $events['future-deadline']['presentation']['end_time']);
        $this->assertSame('September 2, 2026', $events['future-deadline']['presentation']['date_range']);
        $this->assertSame(
            'Development Centre, University Road, Lagos, Nigeria + Zoom',
            $events['future-deadline']['presentation']['location'],
        );
        $this->assertSame(
            'September 1, 2026 at 5:00 PM',
            $events['future-deadline']['presentation']['application_deadline'],
        );
        $this->assertSame('Applications closed', $events['closed-event']['presentation']['application_deadline']);
        $this->assertSame('https://meeting.example.org', $events['meeting-event']['destination']);
        $this->assertTrue($events['meeting-event']['external']);
        $this->assertNull($events['meeting-event']['presentation']['start_time']);
        $this->assertNull($events['meeting-event']['presentation']['application_deadline']);
        $this->assertSame('September 4–6, 2026', $events['meeting-event']['presentation']['date_range']);
        $this->assertSame('Zoom', $events['meeting-event']['presentation']['location']);
    }

    /**
     * @param  list<array<string, mixed>>  $events
     */
    private function service(array $events): EventListingService
    {
        $repository = $this->mock(EventRepository::class);
        $repository->shouldReceive('all')->once()->andReturn($events);

        return new EventListingService($repository);
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function event(string $slug, array $overrides = []): array
    {
        return array_replace_recursive([
            'slug' => $slug,
            'title' => ucfirst(str_replace('-', ' ', $slug)),
            'type' => 'workshop',
            'summary' => 'Development event summary.',
            'featured' => false,
            'timezone' => 'Africa/Lagos',
            'start_date' => '2026-09-10',
            'end_date' => null,
            'schedule' => [
                'mode' => 'single_day',
                'start_time' => '09:00',
                'end_time' => null,
                'itinerary' => [],
            ],
            'location' => [
                'type' => 'physical',
                'venue' => 'Development Venue',
                'address' => null,
                'city' => 'Abuja',
                'country' => 'Nigeria',
                'platform' => null,
            ],
            'application_deadline' => null,
            'meeting_site_url' => null,
            'organiser' => 'Development Organiser',
        ], $overrides);
    }
}

<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Contracts\Content\EventRepository;
use App\Http\Controllers\EventController;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class EventArchiveTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        CarbonImmutable::setTestNow('2026-10-01 12:00:00');
    }

    protected function tearDown(): void
    {
        CarbonImmutable::setTestNow();

        parent::tearDown();
    }

    public function test_archive_returns_only_past_events_in_newest_first_year_and_month_groups(): void
    {
        $this->events([
            $this->event('older-september', ['start_date' => '2026-09-02']),
            $this->event('upcoming-event', ['start_date' => '2026-10-02']),
            $this->event('december-2025', ['start_date' => '2025-12-15']),
            $this->event('newer-september', ['start_date' => '2026-09-20']),
            $this->event('ongoing-event', [
                'start_date' => '2026-10-01',
                'schedule' => ['start_time' => '08:00', 'end_time' => null],
            ]),
            $this->event('cross-month-event', [
                'start_date' => '2026-08-30',
                'end_date' => '2026-09-02',
                'schedule' => ['mode' => 'multi_day', 'start_time' => null, 'end_time' => null],
            ]),
        ]);

        $route = Route::getRoutes()->match(Request::create('/events/archive', 'GET'));
        $response = $this->get('/events/archive');

        $this->assertSame(EventController::class.'@archive', $route->getActionName());
        $this->assertSame('events.archive', $route->getName());
        $response
            ->assertOk()
            ->assertViewIs('events.archive')
            ->assertViewHas('archiveGroups', function (array $groups): bool {
                return array_column($groups, 'year') === ['2026', '2025']
                    && array_column($groups[0]['months'], 'label') === ['September', 'August']
                    && array_column($groups[0]['months'][0]['days'], 'date') === ['2026-09-20', '2026-09-02']
                    && $groups[0]['months'][1]['days'][0]['events'][0]['slug'] === 'cross-month-event'
                    && $groups[1]['months'][0]['label'] === 'December';
            })
            ->assertDontSee('Upcoming event')
            ->assertDontSee('Ongoing event');
    }

    public function test_archive_uses_external_meeting_and_internal_regular_destinations_without_deadlines(): void
    {
        $this->events([
            $this->event('past-regular', [
                'start_date' => '2026-09-10',
                'application_deadline' => '2026-10-15T17:00:00+01:00',
            ]),
            $this->event('past-meeting', [
                'type' => 'meeting',
                'start_date' => '2026-09-12',
                'end_date' => '2026-09-14',
                'schedule' => ['mode' => 'multi_day', 'start_time' => null, 'end_time' => null],
                'application_deadline' => '2026-10-15T17:00:00+01:00',
                'meeting_site_url' => 'https://meeting.example.org',
            ]),
        ]);

        $this->get('/events/archive')
            ->assertOk()
            ->assertSee('href="https://meeting.example.org"', false)
            ->assertSee('target="_blank"', false)
            ->assertSee('rel="noopener noreferrer"', false)
            ->assertSee('href="/events/past-regular"', false)
            ->assertDontSee('Apply before:')
            ->assertDontSee('Applications closed');
    }

    /** @param list<array<string, mixed>> $events */
    private function events(array $events): void
    {
        $repository = $this->mock(EventRepository::class);
        $repository->shouldReceive('all')->once()->andReturn($events);
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
            'image' => null,
            'image_alt' => null,
            'timezone' => 'Africa/Lagos',
            'start_date' => '2026-09-10',
            'end_date' => null,
            'schedule' => [
                'mode' => 'single_day',
                'start_time' => '09:00',
                'end_time' => '11:00',
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

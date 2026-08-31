<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Contracts\Content\EventRepository;
use App\Http\Controllers\EventController;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class EventDetailTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        CarbonImmutable::setTestNow('2026-08-01 12:00:00');
    }

    protected function tearDown(): void
    {
        CarbonImmutable::setTestNow();

        parent::tearDown();
    }

    public function test_regular_multi_day_event_renders_presentation_ready_detail_and_itinerary(): void
    {
        $event = $this->event('multi-day-workshop', [
            'title' => 'Multi-day observation workshop',
            'start_date' => '2026-09-10',
            'end_date' => '2026-09-11',
            'schedule' => [
                'mode' => 'multi_day',
                'start_time' => null,
                'end_time' => null,
                'itinerary' => [
                    [
                        'date' => '2026-09-10',
                        'start_time' => '09:00',
                        'end_time' => '16:00',
                        'title' => 'Observation foundations',
                        'description' => 'Field preparation and instrument checks.',
                    ],
                    [
                        'date' => '2026-09-11',
                        'start_time' => '10:00',
                        'end_time' => null,
                        'title' => null,
                        'description' => 'Analysis and closing discussion.',
                    ],
                ],
            ],
            'location' => [
                'type' => 'hybrid',
                'venue' => 'Development Centre',
                'address' => null,
                'city' => 'Lagos',
                'country' => 'Nigeria',
                'platform' => 'Zoom',
            ],
            'application_deadline' => '2026-08-15T17:00:00+01:00',
            'organiser' => 'NOSEE Development Team',
            'speakers' => ['First Speaker', 'Second Speaker'],
            'registration_url' => 'https://registration.example.org/workshop',
            'body' => 'Editorial detail with <script>alert("unsafe")</script>.',
            'resources' => [
                ['label' => 'Participant guide', 'url' => 'https://resources.example.org/guide'],
            ],
        ]);
        $this->eventRepository()
            ->shouldReceive('find')
            ->once()
            ->with('multi-day-workshop')
            ->andReturn($event);

        $route = Route::getRoutes()->match(Request::create('/events/multi-day-workshop', 'GET'));
        $response = $this->get('/events/multi-day-workshop');
        $html = $response->getContent();

        $this->assertSame(EventController::class.'@show', $route->getActionName());
        $this->assertSame('events.show', $route->getName());
        $response
            ->assertOk()
            ->assertViewIs('events.show')
            ->assertViewHas('event', fn (array $prepared): bool => $prepared['state'] === 'upcoming'
                && array_column($prepared['presentation']['itinerary'], 'date') === ['2026-09-10', '2026-09-11'])
            ->assertSee('bg-orange-100', false)
            ->assertSee('September 10–11, 2026')
            ->assertSee('Development Centre, Lagos, Nigeria + Zoom')
            ->assertSee('Apply before')
            ->assertSee('August 15, 2026 at 5:00 PM')
            ->assertSee('Thursday, September 10, 2026')
            ->assertSee('9:00 AM')
            ->assertSee('4:00 PM')
            ->assertSee('Friday, September 11, 2026')
            ->assertSee('Observation foundations')
            ->assertSee('Analysis and closing discussion.')
            ->assertSee('href="https://registration.example.org/workshop"', false)
            ->assertSee('href="https://resources.example.org/guide"', false)
            ->assertSee('target="_blank"', false)
            ->assertSee('rel="noopener noreferrer"', false)
            ->assertSee('/media/icons/arrow-up-right.svg', false)
            ->assertDontSee('/media/icons/arrow-up-right-stem.svg', false)
            ->assertDontSee('/media/icons/arrow-up-right-head.svg', false)
            ->assertSee('&lt;script&gt;', false)
            ->assertDontSee('<script>', false);

        $this->assertLessThan(strpos($html, 'Second Speaker'), strpos($html, 'First Speaker'));
    }

    public function test_meeting_slug_redirects_externally_and_unknown_slug_returns_not_found(): void
    {
        $repository = $this->eventRepository();
        $repository
            ->shouldReceive('find')
            ->once()
            ->with('meeting-event')
            ->andReturn($this->event('meeting-event', [
                'type' => 'meeting',
                'meeting_site_url' => 'https://meeting.example.org',
            ]));
        $repository
            ->shouldReceive('find')
            ->once()
            ->with('missing-event')
            ->andReturnNull();

        $this->get('/events/meeting-event')
            ->assertRedirect('https://meeting.example.org');
        $this->get('/events/missing-event')
            ->assertNotFound();
    }

    private function eventRepository(): EventRepository
    {
        return $this->mock(EventRepository::class);
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
            'speakers' => [],
            'registration_url' => null,
            'body' => 'Development event body.',
            'resources' => [],
        ], $overrides);
    }
}

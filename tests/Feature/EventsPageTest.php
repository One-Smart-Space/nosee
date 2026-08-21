<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Contracts\Content\EventRepository;
use App\Http\Controllers\EventController;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class EventsPageTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        CarbonImmutable::setTestNow('2026-08-30 12:00:00');
    }

    protected function tearDown(): void
    {
        CarbonImmutable::setTestNow();

        parent::tearDown();
    }

    public function test_route_uses_the_controller_and_forwards_a_trimmed_query_to_the_listing(): void
    {
        $this->events([
            $this->event('lagos-workshop', [
                'title' => 'Lagos observation workshop',
                'location' => ['city' => 'Lagos'],
            ]),
            $this->event('abuja-lecture'),
        ]);

        $route = Route::getRoutes()->match(Request::create('/events', 'GET'));
        $response = $this->get('/events?q=%20Lagos%20');

        $this->assertSame(EventController::class.'@index', $route->getActionName());
        $this->assertSame('events.index', $route->getName());
        $response
            ->assertOk()
            ->assertViewIs('events.index')
            ->assertViewHas('query', 'Lagos')
            ->assertViewHas('upcoming', fn (array $events): bool => array_column($events, 'slug') === ['lagos-workshop'])
            ->assertSee('value="Lagos"', false)
            ->assertSee('Lagos observation workshop');
    }

    public function test_page_renders_featured_ongoing_and_grouped_upcoming_destinations(): void
    {
        $this->events([
            $this->event('ongoing-event', [
                'start_date' => '2026-08-30',
                'schedule' => ['start_time' => '08:00', 'end_time' => null],
            ]),
            $this->event('regular-event', [
                'start_date' => '2026-09-01',
            ]),
            $this->event('featured-meeting', [
                'type' => 'meeting',
                'featured' => true,
                'start_date' => '2026-09-02',
                'end_date' => '2026-09-04',
                'schedule' => ['mode' => 'multi_day', 'start_time' => null, 'end_time' => null],
                'meeting_site_url' => 'https://meeting.example.org',
            ]),
        ]);

        $response = $this->get('/events');
        $html = $response->getContent();

        $response
            ->assertOk()
            ->assertViewHas('featured', fn (array $event): bool => $event['slug'] === 'featured-meeting')
            ->assertViewHas('ongoing', fn (array $events): bool => array_column($events, 'slug') === ['ongoing-event'])
            ->assertViewHas('upcoming_groups', fn (array $groups): bool => $groups[0]['key'] === '2026-09')
            ->assertSee('href="https://meeting.example.org"', false)
            ->assertSee('target="_blank"', false)
            ->assertSee('rel="noopener noreferrer"', false)
            ->assertSee('href="/events/regular-event"', false);

        $this->assertLessThan(strpos($html, 'Upcoming Events'), strpos($html, 'Ongoing Events'));
    }

    public function test_page_exposes_responsive_sticky_archive_and_shared_card_hover_markup(): void
    {
        $this->events([$this->event('timeline-event')]);

        $this->get('/events')
            ->assertOk()
            ->assertSee('data-events-page', false)
            ->assertSee('hidden h-12 w-[416px]', false)
            ->assertSee('data-event-month', false)
            ->assertSee('data-event-day-group', false)
            ->assertSee('sticky top-[var(--compact-navbar-offset)]', false)
            ->assertSee('href="/events/archive"', false)
            ->assertSee('hidden max-w-full lg:inline-flex', false)
            ->assertSee('card-hover-lift', false);
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

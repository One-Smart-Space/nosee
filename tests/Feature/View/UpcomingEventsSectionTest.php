<?php

namespace Tests\Feature\View;

use App\Contracts\Content\EventRepository;
use Illuminate\Support\Facades\Blade;
use Tests\TestCase;

class UpcomingEventsSectionTest extends TestCase
{
    public function test_section_renders_three_featured_upcoming_events_in_order(): void
    {
        $events = $this->events();
        $html = $this->render($events);

        $this->assertStringContainsString('Upcoming Events', $html);
        $this->assertSame(3, substr_count($html, 'data-event-card'));
        $this->assertSame(2, substr_count($html, 'href="/events"'));
        $this->assertSame(2, substr_count($html, 'SEE ALL EVENTS'));

        $previousPosition = -1;
        foreach ($events as $event) {
            $position = strpos($html, 'href="/events/'.$event['slug'].'"');

            $this->assertNotFalse($position);
            $this->assertGreaterThan($previousPosition, $position);
            $previousPosition = $position;
        }
    }

    public function test_section_uses_equal_desktop_columns_and_mobile_stack(): void
    {
        $html = $this->render($this->events());

        $this->assertStringContainsString('bg-default py-8 lg:py-16', $html);
        $this->assertStringContainsString('text-[28px] leading-8 font-semibold', $html);
        $this->assertStringContainsString('lg:text-5xl lg:leading-[56px]', $html);
        $this->assertStringContainsString('grid grid-cols-1 items-stretch gap-5 lg:grid-cols-3 lg:gap-8', $html);
        $this->assertStringContainsString('h-full w-full', $html);
        $this->assertStringContainsString('card-hover-lift', $html);
        $this->assertStringContainsString('hidden lg:block', $html);
        $this->assertStringContainsString('flex justify-center lg:hidden', $html);
        $this->assertStringNotContainsString('<script', $html);
    }

    public function test_section_excludes_non_featured_and_past_events(): void
    {
        $html = $this->render($this->events());

        $this->assertStringNotContainsString('Community Sky Observation Day', $html);
        $this->assertStringNotContainsString('Ionospheric Observation Methods Seminar', $html);
    }

    /** @return list<array<string, mixed>> */
    private function events(): array
    {
        return $this->app->make(EventRepository::class)->featuredUpcoming(3);
    }

    /** @param list<array<string, mixed>> $events */
    private function render(array $events): string
    {
        return Blade::render(
            '<x-home.upcoming-events :$events />',
            compact('events'),
        );
    }
}

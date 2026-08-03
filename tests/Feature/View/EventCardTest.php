<?php

namespace Tests\Feature\View;

use Illuminate\Support\Facades\Blade;
use Tests\TestCase;

class EventCardTest extends TestCase
{
    public function test_card_renders_event_content_as_one_accessible_link(): void
    {
        $event = $this->event('regional-space-weather-conference');
        $html = $this->render($event);

        $this->assertStringContainsString('data-event-card', $html);
        $this->assertStringContainsString('CONFERENCE', $html);
        $this->assertStringContainsString('10.03.2027 - 12.03.2027', $html);
        $this->assertStringContainsString($event['title'], $html);
        $this->assertStringContainsString($event['summary'], $html);
        $this->assertStringContainsString('Abuja, Nigeria', $html);
        $this->assertStringContainsString('href="/events/regional-space-weather-conference"', $html);
        $this->assertStringContainsString('aria-label="View event: '.$event['title'].'"', $html);
        $this->assertSame(1, substr_count($html, '<a '));
        $this->assertStringNotContainsString('<button', $html);
    }

    public function test_card_formats_a_single_day_without_a_range(): void
    {
        $html = $this->render($this->event('atmospheric-measurement-seminar'));

        $this->assertStringContainsString('14.05.2027', $html);
        $this->assertStringNotContainsString('14.05.2027 - 14.05.2027', $html);
    }

    public function test_desktop_raises_on_hover_and_focus_while_mobile_stays_static(): void
    {
        $html = $this->render($this->event('regional-space-weather-conference'));

        $this->assertStringContainsString('lg:hover:-translate-y-1', $html);
        $this->assertStringContainsString('lg:focus-within:-translate-y-1', $html);
        $this->assertStringContainsString('lg:hover:shadow-md', $html);
        $this->assertStringContainsString('duration-300', $html);
        $this->assertStringContainsString('motion-reduce:transition-none', $html);
        $this->assertStringContainsString('motion-reduce:hover:translate-y-0', $html);
        $this->assertStringContainsString('w-full', $html);
        $this->assertStringNotContainsString(' group-hover:-translate-y-1', $html);
    }

    /** @return array<string, mixed> */
    private function event(string $slug): array
    {
        return require base_path("content/events/{$slug}.php");
    }

    /** @param array<string, mixed> $event */
    private function render(array $event): string
    {
        return Blade::render(
            '<x-cards.event-card :$event />',
            compact('event'),
        );
    }
}

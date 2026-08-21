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
        $this->assertStringContainsString('Development Conference Centre, Abuja, Nigeria', $html);
        $this->assertStringContainsString('href="/events/regional-space-weather-conference"', $html);
        $this->assertStringContainsString('aria-label="View event: '.$event['title'].'"', $html);
        $this->assertSame(1, preg_match_all('/<a(?:\s|>)/', $html));
        $this->assertStringNotContainsString('<button', $html);
    }

    public function test_card_formats_a_single_day_without_a_range(): void
    {
        $html = $this->render($this->event('atmospheric-measurement-seminar'));

        $this->assertStringContainsString('14.05.2027', $html);
        $this->assertStringNotContainsString('14.05.2027 - 14.05.2027', $html);
    }

    public function test_card_uses_the_shared_hover_lift(): void
    {
        $html = $this->render($this->event('regional-space-weather-conference'));

        $this->assertStringContainsString('card-hover-lift', $html);
        $this->assertStringContainsString('w-full', $html);
    }

    public function test_meeting_card_links_to_its_external_site(): void
    {
        $event = $this->event('icelli2026');
        $html = $this->render($event);

        $this->assertStringContainsString('href="https://icelli.nosee.org"', $html);
        $this->assertStringNotContainsString('href="/events/icelli2026"', $html);
        $this->assertStringContainsString('Development Meeting Centre, Kano, Nigeria + Zoom', $html);
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

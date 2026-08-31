<?php

namespace Tests\Feature\View;

use Illuminate\Support\Facades\Blade;
use Tests\TestCase;

class DesktopNavigationTest extends TestCase
{
    public function test_navigation_configuration_promotes_events_and_preserves_utility_order(): void
    {
        $navigation = require base_path('content/navigation.php');

        $this->assertSame(
            ['About NSEE', 'Research', 'Data & Products', 'Events', 'Publications', 'Outreach'],
            array_column($navigation['primary'], 'label'),
        );
        $this->assertSame('/events', $navigation['primary'][3]['url']);
        $this->assertSame(
            ['News', 'Multimedia', 'Support NOSEE', 'Login'],
            array_column($navigation['utility'], 'label'),
        );
    }

    public function test_compact_navigation_is_the_default_state(): void
    {
        $html = Blade::render('<x-navigation.desktop-navigation transparent="false" />');

        $this->assertStringContainsString('data-transparent="false"', $html);
        $this->assertStringContainsString('data-navigation-mode="compact"', $html);
        $this->assertStringNotContainsString('data-navigation-expanded-only', $html);
        $this->assertStringNotContainsString('inert', $html);
        $this->assertStringContainsString('src="/logoWhite.png"', $html);
        $this->assertStringContainsString('bg-black/70', $html);
        $this->assertStringContainsString('group-data-[navigation-mode=compact]/navigation:[--desktop-navigation-content-height:4.5rem]', $html);
        $this->assertStringContainsString('h-[var(--desktop-navigation-content-height)] w-auto', $html);
        $this->assertSame(2, substr_count($html, 'h-1/2'));
    }

    public function test_transparent_navigation_is_the_initial_transparent_state(): void
    {
        $html = Blade::render('<x-navigation.desktop-navigation transparent="true" />');

        $this->assertStringContainsString('data-transparent="true"', $html);
        $this->assertStringContainsString('data-navigation-mode="expanded"', $html);
        $this->assertStringContainsString('src="/logoWhite.png"', $html);
        $this->assertStringContainsString('[--desktop-navigation-content-height:5.8rem]', $html);
        $this->assertStringContainsString('xl:[--desktop-navigation-content-height:7.5rem]', $html);
        $this->assertStringNotContainsString('border-b border-white/70', $html);
    }

    public function test_navigation_content_and_dropdown_are_accessible(): void
    {
        $html = Blade::render('<x-navigation.desktop-navigation transparent="true" />');

        foreach (['About NSEE', 'Research', 'Data &amp; Products', 'Events', 'Publications', 'Outreach'] as $label) {
            $this->assertStringContainsString($label, $html);
        }

        foreach (['News', 'Multimedia', 'Support NOSEE', 'Login'] as $label) {
            $this->assertStringContainsString($label, $html);
        }

        $this->assertStringNotContainsString('Meetings', $html);
        $this->assertSame(1, substr_count($html, 'href="/events"'));

        $this->assertStringContainsString('inline-flex h-9 items-center', $html);
        $this->assertStringContainsString('href="/data"', $html);
        $this->assertStringContainsString('href="/products"', $html);
        $this->assertStringContainsString('aria-haspopup="true"', $html);
        $this->assertStringContainsString('group-hover:visible', $html);
        $this->assertStringContainsString('group-focus-within:visible', $html);
        $this->assertStringContainsString('href="/login"', $html);
        $this->assertSame(1, substr_count($html, 'href="/login"'));
        $this->assertStringContainsString('aria-label="Login"', $html);
        $this->assertStringContainsString('h-8 w-px shrink-0 bg-white', $html);
    }

    public function test_meetings_is_not_an_active_public_destination(): void
    {
        $this->get('/meetings')->assertNotFound();
    }
}

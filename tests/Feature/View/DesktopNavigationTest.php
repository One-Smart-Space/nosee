<?php

namespace Tests\Feature\View;

use Illuminate\Support\Facades\Blade;
use Tests\TestCase;

class DesktopNavigationTest extends TestCase
{
    public function test_compact_navigation_is_the_default_state(): void
    {
        $html = Blade::render('<x-navigation.desktop-navigation transparent="false" />');

        $this->assertStringContainsString('data-transparent="false"', $html);
        $this->assertMatchesRegularExpression('/data-navigation-state="transparent"\s+hidden/', $html);
        $this->assertDoesNotMatchRegularExpression('/data-navigation-state="compact"[^>]*\shidden/', $html);
        $this->assertStringContainsString('src="/logo.png"', $html);
    }

    public function test_transparent_navigation_is_the_initial_transparent_state(): void
    {
        $html = Blade::render('<x-navigation.desktop-navigation transparent="true" />');

        $this->assertStringContainsString('data-transparent="true"', $html);
        $this->assertDoesNotMatchRegularExpression('/data-navigation-state="transparent"[^>]*\shidden/', $html);
        $this->assertMatchesRegularExpression('/data-navigation-state="compact"[^>]*\shidden/', $html);
        $this->assertStringContainsString('src="/logoWhite.png"', $html);
        $this->assertStringNotContainsString('border-b border-white/70', $html);
    }

    public function test_navigation_content_and_dropdown_are_accessible(): void
    {
        $html = Blade::render('<x-navigation.desktop-navigation transparent="true" />');

        foreach (['About NSEE', 'Research', 'Data &amp; Products', 'Meetings', 'Publications', 'Outreach'] as $label) {
            $this->assertStringContainsString($label, $html);
        }

        foreach (['News', 'Events', 'Multimedia', 'Support NSEE'] as $label) {
            $this->assertStringContainsString($label, $html);
        }

        $this->assertStringContainsString('inline-flex h-9 items-center', $html);
        $this->assertStringContainsString('href="/data"', $html);
        $this->assertStringContainsString('href="/products"', $html);
        $this->assertStringContainsString('aria-haspopup="true"', $html);
        $this->assertStringContainsString('group-hover:visible', $html);
        $this->assertStringContainsString('group-focus-within:visible', $html);
        $this->assertStringNotContainsString('Login', $html);
        $this->assertStringNotContainsString('profile', strtolower($html));
    }
}

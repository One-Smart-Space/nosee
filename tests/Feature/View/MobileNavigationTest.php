<?php

namespace Tests\Feature\View;

use Illuminate\Support\Facades\Blade;
use Tests\TestCase;

class MobileNavigationTest extends TestCase
{
    public function test_compact_navigation_is_the_default_state(): void
    {
        $html = Blade::render('<x-navigation.mobile-navigation transparent="false" />');

        $this->assertStringContainsString('data-transparent="false"', $html);
        $this->assertMatchesRegularExpression('/data-navigation-state="transparent"\s+hidden/', $html);
        $this->assertDoesNotMatchRegularExpression('/data-navigation-state="compact"[^>]*\shidden/', $html);
        $this->assertStringContainsString('src="/logo.png"', $html);
    }

    public function test_transparent_navigation_is_the_initial_transparent_state(): void
    {
        $html = Blade::render('<x-navigation.mobile-navigation transparent="true" />');

        $this->assertStringContainsString('data-transparent="true"', $html);
        $this->assertDoesNotMatchRegularExpression('/data-navigation-state="transparent"[^>]*\shidden/', $html);
        $this->assertMatchesRegularExpression('/data-navigation-state="compact"[^>]*\shidden/', $html);
        $this->assertStringContainsString('src="/logoWhite.png"', $html);
    }

    public function test_drawer_content_and_submenus_are_accessible(): void
    {
        $html = Blade::render('<x-navigation.mobile-navigation />');

        $this->assertStringContainsString('lg:hidden', $html);
        $this->assertStringContainsString('aria-expanded="false"', $html);
        $this->assertStringContainsString('aria-controls="mobile-navigation-drawer"', $html);
        $this->assertStringContainsString('role="dialog"', $html);
        $this->assertStringContainsString('overflow-y-auto', $html);
        $this->assertSame(3, substr_count($html, 'aria-label="Toggle '));

        foreach (['About NSEE', 'Research', 'Data &amp; Products', 'Meetings', 'Publications', 'Outreach'] as $label) {
            $this->assertStringContainsString($label, $html);
        }

        foreach (['News', 'Events', 'Multimedia', 'Support NSEE'] as $label) {
            $this->assertStringContainsString($label, $html);
        }

        $this->assertStringContainsString('href="/data"', $html);
        $this->assertStringContainsString('href="/products"', $html);
        $this->assertStringNotContainsString('Login', $html);
        $this->assertStringNotContainsString('profile', strtolower($html));
    }

    public function test_layout_passes_one_transparent_value_to_both_navigation_components(): void
    {
        $this->withoutVite();

        $html = view('layouts.app', ['transparentNavigation' => true])->render();

        $this->assertSame(2, substr_count($html, 'data-transparent="true"'));
        $this->assertStringContainsString('data-mobile-navigation', $html);
        $this->assertStringContainsString('data-desktop-navigation', $html);
    }

    public function test_non_transparent_layout_offsets_main_for_both_header_heights(): void
    {
        $this->withoutVite();

        $html = view('layouts.app')->render();

        $this->assertStringContainsString('id="main-content"', $html);
        $this->assertStringContainsString('pt-[4.5rem] lg:pt-16', $html);
        $this->assertStringContainsString('href="#main-content"', $html);
    }

    public function test_transparent_layout_does_not_offset_main(): void
    {
        $this->withoutVite();

        $html = view('layouts.app', ['transparentNavigation' => true])->render();

        $this->assertStringNotContainsString('pt-[4.5rem] lg:pt-16', $html);
    }
}

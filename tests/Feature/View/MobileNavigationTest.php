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
        $this->assertStringContainsString('data-navigation-mode="compact"', $html);
        $this->assertStringContainsString('data-navigation-expanded-only', $html);
        $this->assertStringContainsString('inert', $html);
    }

    public function test_transparent_navigation_is_the_initial_transparent_state(): void
    {
        $html = Blade::render('<x-navigation.mobile-navigation transparent="true" />');

        $this->assertStringContainsString('data-transparent="true"', $html);
        $this->assertStringContainsString('data-navigation-mode="expanded"', $html);
        $this->assertDoesNotMatchRegularExpression('/data-navigation-expanded-only[^>]*\sinert/', $html);
        $this->assertStringContainsString('src="/logoWhite.png"', $html);
        $this->assertStringContainsString('bg-brand', $html);
        $this->assertStringContainsString('bg-black/70', $html);
        $this->assertStringContainsString('!bg-white !text-brand', $html);
        $this->assertStringContainsString('mx-auto h-auto w-full', $html);
        $this->assertStringNotContainsString('border-b border-white/70', $html);
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

        foreach (['About NSEE', 'Research', 'Data &amp; Products', 'Events', 'Publications', 'Outreach'] as $label) {
            $this->assertStringContainsString($label, $html);
        }

        foreach (['News', 'Multimedia', 'Support NOSEE', 'Login'] as $label) {
            $this->assertStringContainsString($label, $html);
        }

        $this->assertStringNotContainsString('Meetings', $html);
        $this->assertSame(1, substr_count($html, 'href="/events"'));

        $this->assertStringContainsString('href="/data"', $html);
        $this->assertStringContainsString('href="/products"', $html);
        $this->assertStringContainsString('href="/login"', $html);
        $this->assertSame(1, substr_count($html, 'href="/login"'));
        $this->assertStringContainsString('aria-label="Login"', $html);
        $this->assertStringContainsString('h-8 w-px shrink-0 bg-white', $html);
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

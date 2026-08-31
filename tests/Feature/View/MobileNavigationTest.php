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

    public function test_layout_starts_both_navigation_variants_expanded(): void
    {
        $this->withoutVite();

        $html = view('layouts.app')->render();

        $this->assertSame(2, substr_count($html, 'data-transparent="true"'));
        $this->assertSame(2, substr_count($html, 'data-navigation-mode="expanded"'));
        $this->assertStringContainsString('data-mobile-navigation', $html);
        $this->assertStringContainsString('data-desktop-navigation', $html);
    }

    public function test_regular_layout_offsets_main_for_the_responsive_expanded_header(): void
    {
        $this->withoutVite();

        $html = view('layouts.app')->render();

        $this->assertStringContainsString('id="main-content"', $html);
        $this->assertStringContainsString('pt-[var(--expanded-navbar-offset)]', $html);
        $this->assertStringContainsString('[--expanded-navbar-offset:clamp(12.3125rem,calc(5.8555rem+32.2266vw),16.5rem)]', $html);
        $this->assertStringContainsString('lg:[--expanded-navbar-offset:8.75rem]', $html);
        $this->assertStringContainsString('xl:[--expanded-navbar-offset:10.25rem]', $html);
        $this->assertStringContainsString('href="#main-content"', $html);
    }

    public function test_homepage_overlay_layout_does_not_offset_main(): void
    {
        $this->withoutVite();

        $html = view('layouts.app', ['navigationOverlaysContent' => true])->render();

        $this->assertStringNotContainsString('pt-[var(--expanded-navbar-offset)]', $html);
    }
}

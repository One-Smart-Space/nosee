<?php

namespace Tests\Feature\View;

use App\Http\Controllers\HomeController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class HomePageTest extends TestCase
{
    public function test_homepage_route_uses_the_controller_and_transparent_layout(): void
    {
        $this->withoutVite();

        $route = Route::getRoutes()->match(Request::create('/', 'GET'));
        $response = $this->get('/');
        $html = $response->getContent();

        $this->assertSame(HomeController::class, $route->getActionName());
        $response
            ->assertOk()
            ->assertViewIs('pages.home')
            ->assertViewHas('heroItems')
            ->assertViewHas('monitoringRecords', fn (array $records): bool => count($records) === 3)
            ->assertSee('data-home-hero', false)
            ->assertSee('data-monitoring-dashboard-section', false)
            ->assertSee('data-transparent="true"', false)
            ->assertDontSee('pt-[4.5rem] lg:pt-16', false);

        $this->assertLessThan(
            strpos($html, 'data-monitoring-dashboard-section'),
            strpos($html, 'data-home-hero'),
        );
    }

    public function test_single_item_hero_is_server_rendered_without_carousel_controls(): void
    {
        $content = require base_path('content/homepage.php');
        $html = Blade::render('<x-home.hero :items="$items" />', [
            'items' => $content['hero']['items'],
        ]);

        $this->assertStringContainsString('Explore NOSEE research and resources', $html);
        $this->assertStringContainsString('h-[600px]', $html);
        $this->assertStringContainsString('lg:h-[850px]', $html);
        $this->assertStringContainsString('text-[28px]', $html);
        $this->assertStringContainsString('lg:text-[64px]', $html);
        $this->assertStringContainsString('object-cover object-center', $html);
        $this->assertStringContainsString('width="1440"', $html);
        $this->assertStringContainsString('height="850"', $html);
        $this->assertStringContainsString('hidden max-w-[294px]', $html);
        $this->assertStringContainsString('lg:block', $html);
        $this->assertStringContainsString('/media/icons/arrow-right-line.svg', $html);
        $this->assertStringContainsString('/media/icons/arrow-right-head.svg', $html);
        $this->assertStringContainsString('aria-roledescription="carousel"', $html);
        $this->assertStringContainsString('aria-label="Slide 1 of 1"', $html);
        $this->assertStringContainsString('fetchpriority="high"', $html);
        $this->assertStringNotContainsString('data-hero-controls', $html);
        $this->assertStringNotContainsString('data-hero-indicator', $html);
    }

    public function test_multiple_items_render_as_accessible_slides_with_selectable_indicators(): void
    {
        $content = require base_path('content/homepage.php');
        $first = $content['hero']['items'][0];
        $second = [
            ...$first,
            'id' => 'second',
            'title' => 'Second hero item',
            'image' => '/media/home/hero-2.png',
        ];
        $html = Blade::render('<x-home.hero :items="$items" />', [
            'items' => [$first, $second],
        ]);

        $this->assertSame(2, substr_count($html, 'data-hero-image-slide'));
        $this->assertSame(2, substr_count($html, 'data-hero-content-slide'));
        $this->assertSame(2, substr_count($html, 'data-hero-indicator'));
        $this->assertStringContainsString('data-slide-id="second"', $html);
        $this->assertStringContainsString('aria-label="Slide 2 of 2"', $html);
        $this->assertStringContainsString('aria-current="true"', $html);
        $this->assertStringContainsString('aria-current="false"', $html);
        $this->assertStringContainsString('Show slide 2: Second hero item', $html);
        $this->assertStringContainsString('loading="lazy"', $html);
        $this->assertSame(1, substr_count($html, 'fetchpriority="high"'));
        $this->assertSame(1, substr_count($html, 'loading="lazy"'));
    }
}

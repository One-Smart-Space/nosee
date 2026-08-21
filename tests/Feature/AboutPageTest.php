<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Http\Controllers\AboutController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class AboutPageTest extends TestCase
{
    public function test_about_page_renders_structured_content_and_local_images(): void
    {
        $route = Route::getRoutes()->match(Request::create('/about', 'GET'));
        $response = $this->get('/about');

        $this->assertSame('about', $route->getName());
        $this->assertSame(AboutController::class, $route->getActionName());

        $response
            ->assertOk()
            ->assertViewIs('about.index')
            ->assertViewHas('about', function (array $about): bool {
                return isset(
                    $about['intro']['headline'],
                    $about['intro']['description_mobile'],
                    $about['intro']['image_tablet'],
                    $about['mission']['statement_mobile'],
                    $about['mission']['objectives'],
                    $about['quote']['text'],
                    $about['quote']['image_tablet'],
                    $about['story']['lead'],
                    $about['leadership']['people'],
                    $about['collaboration']['actions'],
                );
            })
            ->assertSee('data-about-page', false)
            ->assertSee('This mockup treats the page like an editorial feature')
            ->assertSee('Our Mission')
            ->assertSee('John Doe')
            ->assertSee('Our Story')
            ->assertSee('Leadership')
            ->assertSee('Collaboration')
            ->assertSee('/media/about/earth-space-environment-mobile.png', false)
            ->assertSee('/media/about/earth-space-environment-tablet.png', false)
            ->assertSee('/media/about/earth-space-environment.png', false)
            ->assertSee('/media/about/solar-observation-tablet.png', false)
            ->assertSee('/media/about/solar-observation.png', false)
            ->assertSee('/media/about/earth-from-space.png', false);
    }
}

<?php

namespace Tests\Feature\View;

use App\View\Components\Navigation\DesktopNavigation;
use App\View\Components\Navigation\MobileNavigation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Blade;
use Tests\TestCase;

class NavigationActiveStateTest extends TestCase
{
    public function test_root_path_only_marks_the_homepage_current(): void
    {
        $navigation = $this->mobileNavigationAt('/');

        $this->assertTrue($navigation->homeCurrent);

        foreach ([...$navigation->primary, ...$navigation->utility] as $item) {
            $this->assertFalse($item['active']);
            $this->assertFalse($item['current']);
        }
    }

    public function test_exact_and_nested_paths_set_current_and_active_states(): void
    {
        $exact = $this->mobileNavigationAt('/research/climate-science');
        $research = $exact->primary[1];

        $this->assertTrue($research['active']);
        $this->assertFalse($research['current']);
        $this->assertTrue($research['children'][1]['active']);
        $this->assertTrue($research['children'][1]['current']);

        $nested = $this->mobileNavigationAt('/research/climate-science/observations');

        $this->assertTrue($nested->primary[1]['active']);
        $this->assertTrue($nested->primary[1]['children'][1]['active']);
        $this->assertFalse($nested->primary[1]['children'][1]['current']);
    }

    public function test_data_and_products_parent_is_active_for_both_sections(): void
    {
        $data = $this->mobileNavigationAt('/data/archive');
        $products = $this->mobileNavigationAt('/products');

        $this->assertTrue($data->primary[2]['active']);
        $this->assertTrue($data->primary[2]['children'][0]['active']);
        $this->assertTrue($products->primary[2]['active']);
        $this->assertFalse($products->primary[2]['current']);
        $this->assertTrue($products->primary[2]['children'][1]['current']);
    }

    public function test_desktop_and_mobile_receive_identical_active_state(): void
    {
        $this->setRequestPath('/events/annual-meeting');

        $desktop = $this->app->make(DesktopNavigation::class);
        $mobile = $this->app->make(MobileNavigation::class);

        $this->assertSame($desktop->primary, $mobile->primary);
        $this->assertSame($desktop->utility, $mobile->utility);
        $this->assertTrue($desktop->utility[1]['active']);
        $this->assertFalse($desktop->utility[1]['current']);
    }

    public function test_aria_current_is_only_applied_to_the_matching_link(): void
    {
        $this->setRequestPath('/products');

        $desktop = Blade::render('<x-navigation.desktop-navigation />');
        $mobile = Blade::render('<x-navigation.mobile-navigation />');

        foreach ([$desktop, $mobile] as $html) {
            $this->assertMatchesRegularExpression('/href="\/products"\s+aria-current="page"/', $html);
            $this->assertDoesNotMatchRegularExpression(
                '/href="\/data"[^>]*aria-current="page"[^>]*>\s*Data &amp; Products/s',
                $html,
            );
        }

        $this->assertMatchesRegularExpression(
            '/href="\/data"[^>]*class="[^"]*text-brand[^"]*"[^>]*>\s*Data &amp; Products/s',
            $mobile,
        );
        $this->assertStringContainsString('data-mobile-active-submenu', $mobile);
    }

    private function mobileNavigationAt(string $path): MobileNavigation
    {
        $this->setRequestPath($path);

        return $this->app->make(MobileNavigation::class);
    }

    private function setRequestPath(string $path): void
    {
        $request = Request::create($path);

        $this->app->instance('request', $request);
        $this->app->instance(Request::class, $request);
    }
}

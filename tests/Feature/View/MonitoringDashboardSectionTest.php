<?php

namespace Tests\Feature\View;

use App\Contracts\Content\DataRepository;
use Illuminate\Support\Facades\Blade;
use Tests\TestCase;

class MonitoringDashboardSectionTest extends TestCase
{
    public function test_section_renders_the_three_dashboards_and_data_action(): void
    {
        $records = $this->app->make(DataRepository::class)->homepageMonitoring();
        $html = Blade::render(
            '<x-home.monitoring-dashboard :records="$records" />',
            compact('records'),
        );

        $this->assertStringContainsString('EARTH-SPACE ENVIRONMENT MONITORING DASHBOARD', $html);
        $this->assertSame(3, substr_count($html, 'data-monitoring-dashboard-card'));
        $this->assertStringContainsString('Average F-Region Observations.', $html);
        $this->assertStringContainsString('Range Error from TEC', $html);
        $this->assertStringContainsString('TEC Difference from 30-Day Median', $html);
        $this->assertSame(3, substr_count($html, 'Provided by: National Weather Services'));
        $this->assertSame(3, substr_count($html, '/media/monitoringDashboards/'));
        $this->assertStringContainsString('href="/data"', $html);
        $this->assertStringContainsString('ALL DATA', $html);
    }

    public function test_section_uses_responsive_css_only_layout_and_hover_lift(): void
    {
        $records = $this->app->make(DataRepository::class)->homepageMonitoring();
        $html = Blade::render(
            '<x-home.monitoring-dashboard :records="$records" />',
            compact('records'),
        );

        $this->assertStringContainsString('bg-surface py-10 lg:py-16', $html);
        $this->assertStringContainsString('gap-6 lg:gap-12', $html);
        $this->assertStringContainsString('flex-col gap-4 lg:flex-row', $html);
        $this->assertStringContainsString('lg:gap-8', $html);
        $this->assertStringContainsString('lg:flex-1', $html);
        $this->assertStringNotContainsString('grow-[1.5]', $html);
        $this->assertSame(3, substr_count($html, 'card-hover-lift'));
        $this->assertStringNotContainsString('<script', $html);
    }
}

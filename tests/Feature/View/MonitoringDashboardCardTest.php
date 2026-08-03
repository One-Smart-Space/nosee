<?php

namespace Tests\Feature\View;

use Illuminate\Support\Facades\Blade;
use Tests\TestCase;

class MonitoringDashboardCardTest extends TestCase
{
    public function test_card_renders_the_dashboard_and_safe_external_source_link(): void
    {
        $record = require base_path('content/data/items/range-error-from-tec.php');
        $html = Blade::render(
            '<x-cards.monitoring-dashboard-card :record="$record" />',
            compact('record'),
        );

        $this->assertStringContainsString('data-monitoring-dashboard-card', $html);
        $this->assertStringContainsString('Range Error from TEC', $html);
        $this->assertStringContainsString('src="/media/monitoringDashboards/rangeError.png"', $html);
        $this->assertStringContainsString('alt="Global map showing GPS L1 range error derived from total electron content."', $html);
        $this->assertStringContainsString('Provided by: National Weather Services', $html);
        $this->assertStringContainsString('href="https://www.spaceweather.gov/products/glotec"', $html);
        $this->assertStringContainsString('aria-label="View full product: Range Error from TEC"', $html);
        $this->assertStringContainsString('target="_blank"', $html);
        $this->assertStringContainsString('rel="noopener noreferrer"', $html);
        $this->assertStringContainsString('aspect-video', $html);
        $this->assertStringContainsString('border-y border-line-soft', $html);
    }
}

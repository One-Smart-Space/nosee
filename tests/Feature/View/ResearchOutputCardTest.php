<?php

namespace Tests\Feature\View;

use Illuminate\Support\Facades\Blade;
use Tests\TestCase;

class ResearchOutputCardTest extends TestCase
{
    public function test_card_renders_publication_content_and_safe_doi_link(): void
    {
        $record = require base_path('content/publications/low-latitude-geomagnetic-storm-responses.php');
        $html = Blade::render(
            '<x-cards.research-output-card :record="$record" />',
            compact('record'),
        );

        $this->assertStringContainsString('data-research-output-card', $html);
        $this->assertStringContainsString('SPACE WEATHER', $html);
        $this->assertStringContainsString('Low-Latitude Ionospheric Responses to Geomagnetic Storm Events', $html);
        $this->assertStringContainsString('Amina Bello, Tunde Adeyemi, Chika Nwosu', $html);
        $this->assertStringContainsString('2026, Development Journal of Space Environment', $html);
        $this->assertStringContainsString('href="https://doi.org/10.5555/nosee.dev.2026.001"', $html);
        $this->assertStringContainsString('target="_blank"', $html);
        $this->assertStringContainsString('rel="noopener noreferrer"', $html);
        $this->assertStringContainsString('/media/icons/arrow-right.svg', $html);
        $this->assertStringNotContainsString('/media/icons/arrow-right-line.svg', $html);
        $this->assertStringNotContainsString('/media/icons/arrow-right-head.svg', $html);
    }

    public function test_card_uses_mobile_border_and_desktop_only_action(): void
    {
        $record = require base_path('content/publications/low-latitude-geomagnetic-storm-responses.php');
        $html = Blade::render(
            '<x-cards.research-output-card :record="$record" />',
            compact('record'),
        );

        $this->assertStringContainsString('border border-line-soft p-4', $html);
        $this->assertStringContainsString('lg:flex-1', $html);
        $this->assertStringContainsString('lg:border-0 lg:p-0', $html);
        $this->assertStringContainsString('text-lg leading-6 font-medium', $html);
        $this->assertStringContainsString('lg:text-2xl lg:leading-8 lg:font-normal', $html);
        $this->assertMatchesRegularExpression('/class="hidden lg:block"[^>]*>\s*<a/s', $html);
        $this->assertStringContainsString('Read Here', $html);
    }
}

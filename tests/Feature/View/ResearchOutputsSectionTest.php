<?php

namespace Tests\Feature\View;

use App\Contracts\Content\PublicationRepository;
use Illuminate\Support\Facades\Blade;
use Tests\TestCase;

class ResearchOutputsSectionTest extends TestCase
{
    public function test_section_renders_only_the_three_featured_outputs_and_publications_action(): void
    {
        $records = $this->app->make(PublicationRepository::class)->featured(3);
        $html = Blade::render(
            '<x-home.research-outputs :records="$records" />',
            compact('records'),
        );

        $this->assertStringContainsString('Research Outputs', $html);
        $this->assertSame(3, substr_count($html, 'data-research-output-card'));
        $this->assertSame(2, substr_count($html, 'href="/publications"'));
        $this->assertSame(2, substr_count($html, 'SEE ALL OUTPUTS'));

        foreach ($records as $record) {
            $this->assertStringContainsString($record['title'], $html);
            $this->assertStringContainsString(implode(', ', $record['authors']), $html);
            $this->assertStringContainsString($record['publication_source'], $html);
            $this->assertStringContainsString(strtoupper(str_replace('-', ' ', $record['research_area'])), $html);
        }

        $this->assertStringNotContainsString('A Geospatial Data Cube Workflow for Tracking Coastal Change', $html);
    }

    public function test_section_uses_desktop_columns_and_mobile_stack_with_separate_cta_positions(): void
    {
        $records = $this->app->make(PublicationRepository::class)->featured(3);
        $html = Blade::render(
            '<x-home.research-outputs :records="$records" />',
            compact('records'),
        );

        $this->assertStringContainsString('bg-default py-8 lg:py-16', $html);
        $this->assertStringContainsString('gap-8 lg:gap-12', $html);
        $this->assertStringContainsString('grid grid-cols-1 gap-5 lg:grid-cols-3 lg:gap-8', $html);
        $this->assertStringContainsString('text-[28px] leading-8 font-semibold', $html);
        $this->assertStringContainsString('lg:text-5xl lg:leading-[56px]', $html);
        $this->assertMatchesRegularExpression('/class="hidden lg:block"[^>]*data-research-outputs-desktop-cta/', $html);
        $this->assertMatchesRegularExpression('/class="flex justify-center lg:hidden"[^>]*data-research-outputs-mobile-cta/', $html);
        $this->assertStringNotContainsString('<script', $html);
    }
}

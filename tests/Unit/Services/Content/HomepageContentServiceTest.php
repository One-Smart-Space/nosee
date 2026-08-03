<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Content;

use App\Services\Content\HomepageContentService;
use Tests\TestCase;

class HomepageContentServiceTest extends TestCase
{
    public function test_it_loads_the_homepage_hero_items(): void
    {
        $content = $this->app->make(HomepageContentService::class)->get();
        $item = $content['hero']['items'][0];

        $this->assertSame('explore-nosee', $item['id']);
        $this->assertSame('/media/home/hero-1.png', $item['image']);
        $this->assertSame([
            'id',
            'eyebrow',
            'title',
            'summary',
            'image',
            'image_alt',
            'image_credit',
            'cta_label',
            'cta_url',
        ], array_keys($item));
    }

    public function test_it_includes_the_three_homepage_monitoring_records_in_file_order(): void
    {
        $content = $this->app->make(HomepageContentService::class)->get();

        $this->assertCount(3, $content['monitoring']);
        $this->assertSame([
            'average-f-region-observations',
            'range-error-from-tec',
            'tec-difference-from-30-day-median',
        ], array_column($content['monitoring'], 'slug'));
    }

    public function test_it_includes_three_featured_research_outputs_in_date_order(): void
    {
        $content = $this->app->make(HomepageContentService::class)->get();

        $this->assertCount(3, $content['research_outputs']);
        $this->assertSame([
            'low-latitude-geomagnetic-storm-responses',
            'urban-aerosol-optical-depth-patterns',
            'satellite-soil-moisture-drought-signals',
        ], array_column($content['research_outputs'], 'slug'));
    }

    public function test_it_includes_four_unique_trending_articles_with_the_featured_record_first(): void
    {
        $content = $this->app->make(HomepageContentService::class)->get();
        $slugs = array_column($content['trending_news'], 'slug');

        $this->assertCount(4, $slugs);
        $this->assertCount(4, array_unique($slugs));
        $this->assertSame([
            'solar-storm-monitoring-exercise',
            'space-station-instrument-calibration',
            'air-quality-smoke-transport-study',
            'regional-earth-observation-partnership',
        ], $slugs);
    }

    public function test_it_includes_three_featured_upcoming_events_in_start_date_order(): void
    {
        $content = $this->app->make(HomepageContentService::class)->get();

        $this->assertCount(3, $content['upcoming_events']);
        $this->assertSame([
            'regional-space-weather-conference',
            'earth-observation-data-workshop',
            'atmospheric-measurement-seminar',
        ], array_column($content['upcoming_events'], 'slug'));
    }
}

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
}

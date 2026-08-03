<?php

namespace Tests\Feature\View;

use App\Contracts\Content\NewsRepository;
use Illuminate\Support\Facades\Blade;
use Tests\TestCase;

class TrendingNewsSectionTest extends TestCase
{
    public function test_section_renders_four_unique_articles_with_featured_first(): void
    {
        $articles = $this->articles();
        $html = $this->render($articles);
        $slugs = array_column($articles, 'slug');

        $this->assertStringContainsString('What’s Trending', $html);
        $this->assertSame(4, substr_count($html, 'data-trending-news-card'));
        $this->assertCount(4, array_unique($slugs));
        $this->assertLessThan(
            strpos($html, 'data-variant="compact"'),
            strpos($html, 'data-variant="featured"'),
        );

        foreach ($slugs as $slug) {
            $this->assertSame(1, substr_count($html, 'href="/news/'.$slug.'"'));
        }
    }

    public function test_desktop_and_tablet_use_featured_and_compact_groups_with_view_more_tile(): void
    {
        $html = $this->render($this->articles());

        $this->assertStringContainsString('flex flex-col gap-4 md:gap-8 xl:grid xl:grid-cols-2', $html);
        $this->assertStringContainsString('data-trending-news-featured', $html);
        $this->assertStringContainsString('grid grid-cols-1 gap-4 md:grid-cols-2 md:gap-8', $html);
        $this->assertSame(1, substr_count($html, 'data-variant="featured"'));
        $this->assertSame(3, substr_count($html, 'data-variant="compact"'));
        $this->assertStringContainsString('md:col-span-2 md:row-span-2', $html);
        $this->assertStringContainsString('data-trending-news-view-more', $html);
        $this->assertStringContainsString('hidden aspect-square', $html);
        $this->assertStringContainsString('hover:bg-brand hover:text-inverse', $html);
        $this->assertStringContainsString('VIEW MORE', $html);
    }

    public function test_mobile_uses_four_compact_presentations_and_standard_news_action(): void
    {
        $html = $this->render($this->articles());

        $this->assertSame(4, substr_count($html, 'data-mobile-variant="compact"'));
        $this->assertStringContainsString('class="grid grid-cols-1 gap-4 md:grid-cols-2 md:gap-8"', $html);
        $this->assertStringContainsString('flex justify-center md:hidden', $html);
        $this->assertStringContainsString('VIEW ALL NEWS', $html);
        $this->assertSame(2, substr_count($html, 'href="/news"'));
        $this->assertStringNotContainsString('<script', $html);
    }

    /** @return list<array<string, mixed>> */
    private function articles(): array
    {
        return $this->app->make(NewsRepository::class)->homepageTrending(4);
    }

    /** @param list<array<string, mixed>> $articles */
    private function render(array $articles): string
    {
        return Blade::render(
            '<x-home.trending-news :$articles />',
            compact('articles'),
        );
    }
}

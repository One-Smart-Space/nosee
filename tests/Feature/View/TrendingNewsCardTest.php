<?php

namespace Tests\Feature\View;

use Illuminate\Support\Facades\Blade;
use InvalidArgumentException;
use Tests\TestCase;

class TrendingNewsCardTest extends TestCase
{
    public function test_featured_variant_renders_the_article_as_one_accessible_link(): void
    {
        $article = $this->article();
        $html = $this->render($article, 'featured');

        $this->assertStringContainsString('data-trending-news-card', $html);
        $this->assertStringContainsString('data-variant="featured"', $html);
        $this->assertStringContainsString('md:col-span-2 md:row-span-2', $html);
        $this->assertStringContainsString('href="/news/solar-storm-monitoring-exercise"', $html);
        $this->assertStringContainsString('aria-label="Read more: Regional Teams Complete Solar Storm Monitoring Exercise"', $html);
        $this->assertSame(1, substr_count($html, '<a '));
        $this->assertStringNotContainsString('<button', $html);
    }

    public function test_compact_variant_renders_image_metadata_title_excerpt_and_action(): void
    {
        $article = $this->article();
        $html = $this->render($article, 'compact');

        $this->assertStringContainsString('data-variant="compact"', $html);
        $this->assertStringContainsString('src="/media/trending/trending1.png"', $html);
        $this->assertStringContainsString('alt="Bright solar flare extending from the surface of the Sun"', $html);
        $this->assertStringContainsString('Organisation News', $html);
        $this->assertStringContainsString('01/08/2026', $html);
        $this->assertStringContainsString('Regional Teams Complete Solar Storm Monitoring Exercise', $html);
        $this->assertStringContainsString($article['excerpt'], $html);
        $this->assertStringContainsString('Read more', $html);
        $this->assertStringContainsString('/media/icons/arrow-right.svg', $html);
        $this->assertStringNotContainsString('/media/icons/arrow-right-line.svg', $html);
        $this->assertStringNotContainsString('/media/icons/arrow-right-head.svg', $html);
        $this->assertStringNotContainsString('md:col-span-2 md:row-span-2', $html);
    }

    public function test_unsupported_variant_is_rejected(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->render($this->article(), 'unsupported');
    }

    /** @return array<string, mixed> */
    private function article(): array
    {
        return require base_path('content/news/solar-storm-monitoring-exercise.php');
    }

    /** @param array<string, mixed> $article */
    private function render(array $article, string $variant): string
    {
        return Blade::render(
            '<x-cards.trending-news-card :$article :$variant />',
            compact('article', 'variant'),
        );
    }
}

<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Content;

use App\Services\Content\ContentSchemaRegistry;
use App\Services\Content\ContentValidationService;
use App\Services\Content\FooterContentService;
use Tests\TestCase;

class FooterContentTest extends TestCase
{
    public function test_site_footer_content_is_valid_and_uses_the_required_links(): void
    {
        $site = require base_path('content/site.php');

        $this->assertSame([], $this->validate($site));
        $this->assertSame(
            ['Quick Links'],
            array_column($site['footer']['link_groups'], 'heading'),
        );
        $this->assertSame(
            [['Mission & Vision', 'Research', 'People', 'News', 'Gallery']],
            array_map(
                fn (array $group): array => array_column($group['links'], 'label'),
                $site['footer']['link_groups'],
            ),
        );
        $this->assertSame(
            ['/privacy', '/terms'],
            array_column($site['footer']['legal_links'], 'url'),
        );
        $this->assertTrue($site['footer']['newsletter']['enabled']);
        $this->assertSame('/newsletter/subscribe', $site['footer']['newsletter']['action']);
        $this->assertSame('info@nosee.org', $site['footer']['contact']['email']);
        $this->assertSame(
            ['https://linkedin.com', 'https://facebook.com', 'https://x.com', 'https://instagram.com', 'https://youtube.com'],
            array_column($site['footer']['social_links'], 'url'),
        );
    }

    public function test_footer_schema_rejects_invalid_urls_email_and_boolean_values(): void
    {
        $site = require base_path('content/site.php');
        $site['footer']['contact']['email'] = 'not-an-email';
        $site['footer']['social_links'][0]['url'] = 'not-a-url';
        $site['footer']['newsletter']['enabled'] = 0;

        $errors = $this->validate($site);

        $this->assertErrorContains($errors, 'valid email address');
        $this->assertErrorContains($errors, 'valid URL');
        $this->assertErrorContains($errors, 'must be a boolean');
    }

    public function test_enabled_newsletter_requires_a_provider_action(): void
    {
        $site = require base_path('content/site.php');
        $site['footer']['newsletter']['enabled'] = true;
        $site['footer']['newsletter']['action'] = null;

        $this->assertErrorContains(
            $this->validate($site),
            'footer.newsletter.action',
        );
    }

    public function test_footer_service_returns_only_validated_footer_content(): void
    {
        $footer = $this->app->make(FooterContentService::class)->get();

        $this->assertSame([
            'description',
            'link_groups',
            'contact',
            'social_links',
            'legal_links',
            'newsletter',
            'support_url',
            'copyright',
        ], array_keys($footer));
        $this->assertSame('Quick Links', $footer['link_groups'][0]['heading']);
    }

    /** @param  array<string, mixed>  $site */
    private function validate(array $site): array
    {
        return $this->app->make(ContentValidationService::class)->validateRecord(
            ContentSchemaRegistry::SITE,
            $site,
        );
    }

    /** @param  list<string>  $errors */
    private function assertErrorContains(array $errors, string $expected): void
    {
        $this->assertNotEmpty(array_filter(
            $errors,
            fn (string $error): bool => str_contains(strtolower($error), strtolower($expected)),
        ));
    }
}

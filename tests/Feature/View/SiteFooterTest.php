<?php

namespace Tests\Feature\View;

use Illuminate\Support\Facades\Blade;
use Tests\TestCase;

class SiteFooterTest extends TestCase
{
    public function test_layout_composer_injects_the_footer_after_main(): void
    {
        $this->withoutVite();

        $html = Blade::render(<<<'BLADE'
            @extends('layouts.app')
            @section('content')<p>Page content</p>@endsection
        BLADE);

        $this->assertStringContainsString('data-site-footer', $html);
        $this->assertStringContainsString('Quick Links', $html);
        $this->assertStringContainsString('Plot 003, Saburi One Kubwa-Deidei Express Road 900107 Abuja Federal Capital Territory, Nigeria', $html);
        $this->assertStringContainsString('info@nosee.org', $html);
        $this->assertStringContainsString('href="/support"', $html);
        $this->assertStringContainsString('href="/privacy"', $html);
        $this->assertStringContainsString('2026 NOSEE. All rights reserved.', $html);
        $this->assertMatchesRegularExpression('/href="https:\/\/linkedin\.com"[\s\S]*?target="_blank"[\s\S]*?rel="noopener noreferrer"/', $html);
        $this->assertLessThan(strpos($html, 'data-site-footer'), strpos($html, '</main>'));
    }

    public function test_it_renders_the_responsive_footer_structure_and_fixture_content(): void
    {
        $html = $this->render();

        $this->assertStringContainsString('data-site-footer', $html);
        $this->assertStringContainsString('data-footer-main', $html);
        $this->assertStringContainsString('grid gap-12 lg:grid-cols-12 lg:gap-8', $html);
        $this->assertStringContainsString('order-2 lg:order-4 lg:col-span-4', $html);
        $this->assertStringContainsString('order-3 lg:order-2 lg:col-span-3', $html);
        $this->assertStringContainsString('overflow-x-clip', $html);
        $this->assertStringContainsString('src="/logoWhite.png"', $html);
        $this->assertStringContainsString('width="160"', $html);
        $this->assertStringContainsString('h-auto w-40 max-w-full', $html);
        $this->assertStringContainsString('NOSEE connects researchers working across space and Earth systems.', $html);
        $this->assertStringContainsString('Quick Links', $html);
        $this->assertStringContainsString('Plot 003, Saburi One Kubwa-Deidei Express Road 900107 Abuja Federal Capital Territory, Nigeria', $html);
        $this->assertStringContainsString('info@nosee.org', $html);
        $this->assertStringContainsString('+234 805 929 1023', $html);
        $this->assertStringContainsString('LinkedIn', $html);
        $this->assertStringContainsString('size-6 bg-tertiary', $html);
        $this->assertStringContainsString('rounded-sm p-2', $html);
        $this->assertStringContainsString('Support NOSEE', $html);
        $this->assertStringContainsString('NOSEE Newsletter', $html);
        $this->assertStringContainsString('Privacy Policy', $html);
        $this->assertStringContainsString('2026. All rights reserved.', $html);
    }

    public function test_desktop_links_share_one_expanded_hit_area_and_animated_underline(): void
    {
        $html = $this->render();

        $this->assertStringContainsString('lg:after:-inset-y-1', $html);
        $this->assertStringContainsString('lg:after:bg-[linear-gradient(currentColor,currentColor)]', $html);
        $this->assertStringContainsString('lg:after:bg-[length:0_1px]', $html);
        $this->assertStringContainsString('lg:hover:after:bg-[length:100%_1px]', $html);
        $this->assertStringContainsString('lg:focus-visible:after:bg-[length:100%_1px]', $html);
        $this->assertStringContainsString('hover:text-inverse', $html);
        $this->assertStringContainsString('focus-visible:outline-accent', $html);

        // Pseudo-element utilities are desktop-prefixed, so mobile links retain no animated underline.
        $this->assertDoesNotMatchRegularExpression('/(?:^|\s)(?:hover:|focus-visible:)?after:/', $html);
    }

    public function test_external_links_are_safe_and_controls_are_not_nested(): void
    {
        $html = $this->render();

        $this->assertMatchesRegularExpression('/href="https:\/\/linkedin\.com"[\s\S]*?target="_blank"[\s\S]*?rel="noopener noreferrer"/', $html);
        $this->assertMatchesRegularExpression('/href="https:\/\/support\.nosee\.test"[\s\S]*?target="_blank"[\s\S]*?rel="noopener noreferrer"/', $html);
        $this->assertDoesNotMatchRegularExpression('/<a\b[^>]*>(?:(?!<\/a>).)*<button\b/s', $html);
        $this->assertDoesNotMatchRegularExpression('/<button\b[^>]*>(?:(?!<\/button>).)*<a\b/s', $html);
    }

    public function test_newsletter_input_has_an_accessible_label(): void
    {
        $html = $this->render();

        $this->assertStringContainsString('<label for="footer-newsletter-email"', $html);
        $this->assertStringContainsString('Newsletter email address', $html);
        $this->assertStringContainsString('id="footer-newsletter-email"', $html);
        $this->assertStringContainsString('type="email"', $html);
        $this->assertStringContainsString('<button type="submit"', $html);
        $this->assertStringContainsString('data-newsletter-dialog', $html);
        $this->assertStringContainsString("Thank you for subscribing! Look out for NOSEE's newletter in your mailbox", $html);
    }

    public function test_disabled_newsletter_keeps_its_appearance_without_submitting(): void
    {
        $footer = (require base_path('content/site.php'))['footer'];
        $footer['newsletter']['enabled'] = false;
        $footer['newsletter']['action'] = null;
        $html = Blade::render(
            '<x-footer.site-footer :description="$footer[\'description\']" :link-groups="$footer[\'link_groups\']" :contact="$footer[\'contact\']" :social-links="$footer[\'social_links\']" :legal-links="$footer[\'legal_links\']" :newsletter="$footer[\'newsletter\']" :support-url="$footer[\'support_url\']" :copyright="$footer[\'copyright\']" />',
            compact('footer'),
        );

        $this->assertStringContainsString('data-newsletter-enabled="false"', $html);
        $this->assertStringContainsString('aria-disabled="true"', $html);
        $this->assertStringContainsString('Newsletter signup is unavailable in this development build.', $html);
        $this->assertMatchesRegularExpression('/<input[^>]*disabled[^>]*>/', $html);
        $this->assertMatchesRegularExpression('/<button[^>]*disabled[^>]*>/', $html);
        $this->assertDoesNotMatchRegularExpression('/<form(?=[^>]*data-newsletter-enabled="false")[^>]*(?:action|method)=/', $html);
        $this->assertStringNotContainsString('data-newsletter-success', $html);
    }

    private function render(): string
    {
        $description = 'NOSEE connects researchers working across space and Earth systems.';
        $linkGroups = [[
            'heading' => 'Quick Links',
            'links' => [
                ['label' => 'Mission & Vision', 'url' => '/about#mission'],
                ['label' => 'Research', 'url' => '/research'],
                ['label' => 'People', 'url' => '/people'],
                ['label' => 'News', 'url' => '/news'],
                ['label' => 'Gallery', 'url' => '/multimedia'],
            ],
        ]];
        $contact = [
            'heading' => 'Contact',
            'address' => 'Plot 003, Saburi One Kubwa-Deidei Express Road 900107 Abuja Federal Capital Territory, Nigeria',
            'email' => 'info@nosee.org',
            'phone' => '+234 805 929 1023',
        ];
        $socialLinks = [[
            'label' => 'LinkedIn',
            'url' => 'https://linkedin.com',
            'icon' => '/media/icons/linkedin-fill.svg',
        ]];
        $legalLinks = [
            ['label' => 'Privacy Policy', 'url' => '/privacy'],
            ['label' => 'Terms of Use', 'url' => '/terms'],
        ];
        $newsletter = [
            'heading' => 'NOSEE Newsletter',
            'description' => 'Subscribe to our newsletter to receive more updates.',
            'label' => 'Newsletter email address',
            'placeholder' => 'Email address',
            'button_label' => 'Subscribe',
            'enabled' => true,
            'action' => 'https://newsletter.example.test/subscribe',
            'disabled_text' => 'Newsletter signup is unavailable.',
        ];
        $supportUrl = 'https://support.nosee.test';
        $copyright = '© 2026. All rights reserved.';

        return Blade::render(
            '<x-footer.site-footer :$description :$linkGroups :$contact :$socialLinks :$legalLinks :$newsletter :$supportUrl :$copyright />',
            compact(
                'description',
                'linkGroups',
                'contact',
                'socialLinks',
                'legalLinks',
                'newsletter',
                'supportUrl',
                'copyright',
            ),
        );
    }
}

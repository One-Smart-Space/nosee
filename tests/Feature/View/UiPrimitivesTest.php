<?php

namespace Tests\Feature\View;

use Illuminate\Support\Facades\Blade;
use InvalidArgumentException;
use Tests\TestCase;

class UiPrimitivesTest extends TestCase
{
    public function test_the_application_layout_renders(): void
    {
        $this->withoutVite();

        $html = Blade::render(<<<'BLADE'
            @extends('layouts.app')
            @section('title', 'NOSEE Test')
            @section('content')<p>Content</p>@endsection
        BLADE);

        $this->assertStringContainsString('<title>NOSEE Test</title>', $html);
        $this->assertStringContainsString('<main id="main-content">', $html);
        $this->assertStringContainsString('Skip to main content', $html);
    }

    public function test_every_button_option_renders(): void
    {
        foreach (['sm', 'base', 'lg'] as $size) {
            foreach (['primary', 'secondary', 'destructive'] as $intent) {
                foreach (['fill', 'stroke', 'text'] as $variant) {
                    foreach (['none', 'left', 'right', 'only'] as $iconPosition) {
                        $html = Blade::render(<<<'BLADE'
                            <x-ui.button :$size :$intent :$variant :$iconPosition>
                                <x-slot:icon><svg aria-hidden="true"></svg></x-slot:icon>
                                Label
                            </x-ui.button>
                        BLADE, compact('size', 'intent', 'variant', 'iconPosition'));

                        $this->assertStringContainsString('<button', $html);
                    }
                }
            }
        }
    }

    public function test_disabled_links_do_not_navigate(): void
    {
        $html = Blade::render('<x-ui.button href="/target" disabled>Label</x-ui.button>');

        $this->assertStringNotContainsString('href="/target"', $html);
        $this->assertStringContainsString('aria-disabled="true"', $html);
        $this->assertStringContainsString('tabindex="-1"', $html);
    }

    public function test_unsupported_button_options_are_rejected(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Blade::render('<x-ui.button size="unsupported">Label</x-ui.button>');
    }
}

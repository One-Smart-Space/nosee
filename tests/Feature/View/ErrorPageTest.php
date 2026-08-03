<?php

namespace Tests\Feature\View;

use Tests\TestCase;

class ErrorPageTest extends TestCase
{
    public function test_not_found_pages_keep_the_application_navigation(): void
    {
        $this->withoutVite();

        $this->get('/not-a-route')
            ->assertNotFound()
            ->assertSee('data-desktop-navigation', false)
            ->assertSee('data-mobile-navigation', false)
            ->assertSee('data-site-footer', false);
    }
}

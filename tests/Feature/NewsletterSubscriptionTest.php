<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class NewsletterSubscriptionTest extends TestCase
{
    public function test_valid_subscription_is_written_to_the_development_log(): void
    {
        Log::spy();
        Log::shouldReceive('channel')
            ->once()
            ->with('stderr')
            ->andReturnSelf();

        $this->postJson('/newsletter/subscribe', [
            'email' => 'reader@example.com',
        ])
            ->assertOk()
            ->assertJson([
                'message' => 'Newsletter subscription received.',
            ]);

        Log::shouldHaveReceived('info')
            ->once()
            ->with('Newsletter subscription received.', [
                'email' => 'reader@example.com',
            ]);
    }

    public function test_invalid_subscription_is_rejected_without_logging(): void
    {
        Log::spy();

        $this->postJson('/newsletter/subscribe', [
            'email' => 'not-an-email',
        ])->assertUnprocessable();

        Log::shouldNotHaveReceived('info');
        Log::shouldNotHaveReceived('channel');
    }
}

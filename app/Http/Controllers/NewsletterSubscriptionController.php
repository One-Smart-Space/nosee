<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

final class NewsletterSubscriptionController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email:rfc', 'max:254'],
        ]);

        // Send development subscriptions to the running application's terminal without persistence.
        Log::channel('stderr')->info('Newsletter subscription received.', [
            'email' => $validated['email'],
        ]);

        return response()->json([
            'message' => 'Newsletter subscription received.',
        ]);
    }
}

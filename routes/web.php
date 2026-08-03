<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\NewsletterSubscriptionController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class);
Route::post('/newsletter/subscribe', NewsletterSubscriptionController::class)
    ->middleware('throttle:10,1')
    ->name('newsletter.subscribe');

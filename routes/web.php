<?php

use App\Http\Controllers\AboutController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\NewsletterSubscriptionController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class);
Route::get('/about', AboutController::class)->name('about');
Route::get('/events', [EventController::class, 'index'])->name('events.index');
Route::get('/events/archive', [EventController::class, 'archive'])->name('events.archive');
Route::get('/events/{slug}', [EventController::class, 'show'])->name('events.show');
Route::post('/newsletter/subscribe', NewsletterSubscriptionController::class)
    ->middleware('throttle:10,1')
    ->name('newsletter.subscribe');

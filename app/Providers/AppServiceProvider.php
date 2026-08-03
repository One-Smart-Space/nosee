<?php

namespace App\Providers;

use App\Services\Content\FooterContentService;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\View\View as LayoutView;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Load validated shared footer content only when the application layout renders.
        View::composer('layouts.app', static function (LayoutView $view): void {
            $view->with('footer', app(FooterContentService::class)->get());
        });
    }
}

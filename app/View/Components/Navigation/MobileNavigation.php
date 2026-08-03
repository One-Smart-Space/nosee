<?php

declare(strict_types=1);

namespace App\View\Components\Navigation;

use Illuminate\Contracts\View\View;

final class MobileNavigation extends Navigation
{
    public function render(): View
    {
        return view('components.navigation.mobile-navigation');
    }
}

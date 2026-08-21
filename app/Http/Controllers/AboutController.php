<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\Content\AboutContentService;
use Illuminate\Contracts\View\View;

final class AboutController extends Controller
{
    public function __invoke(AboutContentService $content): View
    {
        return view('about.index', [
            'about' => $content->get(),
        ]);
    }
}

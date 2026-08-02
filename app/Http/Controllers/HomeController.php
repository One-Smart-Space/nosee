<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\Content\HomepageContentService;
use Illuminate\Contracts\View\View;

final class HomeController extends Controller
{
    public function __invoke(HomepageContentService $content): View
    {
        $homepage = $content->get();

        // Keep file access behind the content service and pass only view-ready records.
        return view('pages.home', [
            'heroItems' => $homepage['hero']['items'],
            'monitoringRecords' => $homepage['monitoring'],
            'transparentNavigation' => true,
        ]);
    }
}

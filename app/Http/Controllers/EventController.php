<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\Events\EventDetailService;
use App\Services\Events\EventListingService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class EventController extends Controller
{
    public function index(Request $request, EventListingService $events): View
    {
        $query = $request->query('q');
        $query = is_string($query) ? trim($query) : null;

        return view('events.index', $events->build($query));
    }

    public function archive(EventListingService $events): View
    {
        return view('events.archive', [
            'archiveGroups' => $events->archive(),
        ]);
    }

    public function show(string $slug, EventDetailService $events): View|RedirectResponse
    {
        $event = $events->find($slug);

        abort_if($event === null, 404);

        if ($event['type'] === 'meeting') {
            return redirect()->away($event['meeting_site_url']);
        }

        return view('events.show', ['event' => $event]);
    }
}

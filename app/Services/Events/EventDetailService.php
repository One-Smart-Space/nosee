<?php

declare(strict_types=1);

namespace App\Services\Events;

use App\Contracts\Content\EventRepository;
use Carbon\CarbonImmutable;

final class EventDetailService
{
    public function __construct(
        private readonly EventRepository $events,
        private readonly EventListingService $listing,
    ) {}

    /** @return array<string, mixed>|null */
    public function find(string $slug, ?CarbonImmutable $now = null): ?array
    {
        $event = $this->events->find($slug);

        return $event === null
            ? null
            : $this->listing->present($event, $now, includePastDeadline: true);
    }
}

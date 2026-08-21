<?php

declare(strict_types=1);

namespace App\Services\Events;

use App\Contracts\Content\EventRepository;
use Carbon\CarbonImmutable;

final class EventListingService
{
    public function __construct(private readonly EventRepository $events) {}

    /**
     * @return array{
     *     featured: array<string, mixed>|null,
     *     ongoing: list<array<string, mixed>>,
     *     upcoming: list<array<string, mixed>>,
     *     upcoming_groups: list<array<string, mixed>>,
     *     query: string|null
     * }
     */
    public function build(?string $query = null, ?CarbonImmutable $now = null): array
    {
        $now ??= CarbonImmutable::now();
        $query = $query === null ? null : trim($query);
        $query = $query === '' ? null : $query;
        $records = array_map(
            fn (array $event): array => $this->present($event, $now),
            $this->events->all(),
        );

        $ongoing = array_values(array_filter(
            $records,
            static fn (array $event): bool => $event['state'] === 'ongoing',
        ));
        $upcoming = array_values(array_filter(
            $records,
            static fn (array $event): bool => $event['state'] === 'upcoming',
        ));

        usort($ongoing, $this->compare(...));
        usort($upcoming, $this->compare(...));

        $featured = $this->featured($upcoming);

        if ($query !== null) {
            $upcoming = array_values(array_filter(
                $upcoming,
                fn (array $event): bool => $this->matchesQuery($event, $query),
            ));
        }

        return [
            'featured' => $featured,
            'ongoing' => $ongoing,
            'upcoming' => $upcoming,
            'upcoming_groups' => $this->groupByStartMonth($upcoming),
            'query' => $query,
        ];
    }

    /**
     * @return list<array{year: string, months: list<array<string, mixed>>}>
     */
    public function archive(?CarbonImmutable $now = null): array
    {
        $now ??= CarbonImmutable::now();
        $past = array_values(array_filter(
            array_map(
                fn (array $event): array => $this->present($event, $now),
                $this->events->all(),
            ),
            static fn (array $event): bool => $event['state'] === 'past',
        ));

        usort($past, fn (array $left, array $right): int => $this->compare($right, $left));

        return $this->groupArchive($past);
    }

    /**
     * @param  array<string, mixed>  $event
     * @return array<string, mixed>
     */
    public function present(
        array $event,
        ?CarbonImmutable $now = null,
        bool $includePastDeadline = false,
    ): array {
        $now ??= CarbonImmutable::now();
        $state = $this->state($event, $now);
        $isMeeting = $event['type'] === 'meeting';

        return [
            ...$event,
            'state' => $state,
            'destination' => $isMeeting
                ? $event['meeting_site_url']
                : '/events/'.$event['slug'],
            'external' => $isMeeting,
            'presentation' => [
                'start_time' => $this->formatTime($event['schedule']['start_time']),
                'end_time' => $this->formatTime($event['schedule']['end_time']),
                'date_range' => $this->formatDateRange($event),
                'start_date' => $this->dateParts($event['start_date'], $event['timezone']),
                'end_date' => $this->dateParts(
                    $event['end_date'] ?? $event['start_date'],
                    $event['timezone'],
                ),
                'location' => $this->formatLocation($event['location']),
                'application_deadline' => $this->formatApplicationDeadline(
                    $event,
                    $state,
                    $now,
                    $includePastDeadline,
                ),
                'itinerary' => $this->formatItinerary($event['schedule']['itinerary'], $event['timezone']),
                'type' => config('event-types.'.$event['type']),
            ],
        ];
    }

    /** @param array<string, mixed> $event */
    private function state(array $event, CarbonImmutable $now): string
    {
        $localNow = $now->setTimezone($event['timezone']);

        if ($event['type'] === 'meeting' || $event['schedule']['mode'] === 'multi_day') {
            $today = $localNow->toDateString();
            $endDate = $event['end_date'] ?? $event['start_date'];

            return match (true) {
                $today < $event['start_date'] => 'upcoming',
                $today > $endDate => 'past',
                default => 'ongoing',
            };
        }

        $start = CarbonImmutable::parse(
            $event['start_date'].' '.$event['schedule']['start_time'],
            $event['timezone'],
        );
        $end = $event['schedule']['end_time'] === null
            ? $start->endOfDay()
            : CarbonImmutable::parse(
                $event['start_date'].' '.$event['schedule']['end_time'],
                $event['timezone'],
            );

        return match (true) {
            $localNow->lessThan($start) => 'upcoming',
            $localNow->greaterThan($end) => 'past',
            default => 'ongoing',
        };
    }

    /**
     * @param  array<string, mixed>  $left
     * @param  array<string, mixed>  $right
     */
    private function compare(array $left, array $right): int
    {
        return strcmp($this->sortKey($left), $this->sortKey($right))
            ?: strcmp($left['slug'], $right['slug']);
    }

    /** @param array<string, mixed> $event */
    private function sortKey(array $event): string
    {
        $time = $event['type'] !== 'meeting' && $event['schedule']['mode'] === 'single_day'
            ? $event['schedule']['start_time']
            : '00:00';

        return $event['start_date'].' '.$time;
    }

    /**
     * @param  list<array<string, mixed>>  $upcoming
     * @return array<string, mixed>|null
     */
    private function featured(array $upcoming): ?array
    {
        foreach ($upcoming as $event) {
            if ($event['featured'] === true && $event['type'] === 'meeting') {
                return $event;
            }
        }

        return $upcoming[0] ?? null;
    }

    /** @param array<string, mixed> $event */
    private function matchesQuery(array $event, string $query): bool
    {
        $location = $event['location'];
        $values = [
            $event['title'],
            $event['summary'],
            $event['type'],
            $location['venue'],
            $location['address'],
            $location['city'],
            $location['country'],
            $location['platform'],
            $event['organiser'],
        ];

        foreach ($values as $value) {
            if (is_string($value) && stripos($value, $query) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  list<array<string, mixed>>  $events
     * @return list<array<string, mixed>>
     */
    private function groupByStartMonth(array $events): array
    {
        $groups = [];

        foreach ($events as $event) {
            $date = CarbonImmutable::parse($event['start_date'], $event['timezone']);
            $monthKey = $date->format('Y-m');
            $dayKey = $date->toDateString();

            $groups[$monthKey] ??= [
                'key' => $monthKey,
                'label' => $date->format('F Y'),
                'days' => [],
            ];
            $groups[$monthKey]['days'][$dayKey] ??= [
                'date' => $dayKey,
                'label' => strtoupper($date->format('d M')),
                'events' => [],
            ];
            $groups[$monthKey]['days'][$dayKey]['events'][] = $event;
        }

        return array_values(array_map(
            static fn (array $group): array => [
                ...$group,
                'days' => array_values($group['days']),
            ],
            $groups,
        ));
    }

    /**
     * @param  list<array<string, mixed>>  $events
     * @return list<array{year: string, months: list<array<string, mixed>>}>
     */
    private function groupArchive(array $events): array
    {
        $groups = [];

        foreach ($this->groupByStartMonth($events) as $month) {
            $date = CarbonImmutable::parse($month['days'][0]['date']);
            $year = $date->format('Y');
            $groups[$year] ??= [
                'year' => $year,
                'months' => [],
            ];
            $groups[$year]['months'][] = [
                ...$month,
                'label' => $date->format('F'),
            ];
        }

        return array_values($groups);
    }

    private function formatTime(?string $time): ?string
    {
        return $time === null
            ? null
            : CarbonImmutable::parse($time)->format('g:i A');
    }

    /** @return array{day: string, month: string} */
    private function dateParts(string $date, string $timezone): array
    {
        $parsed = CarbonImmutable::parse($date, $timezone);

        return [
            'day' => $parsed->format('d'),
            'month' => strtoupper($parsed->format('M')),
        ];
    }

    /** @param array<string, mixed> $event */
    private function formatDateRange(array $event): string
    {
        $start = CarbonImmutable::parse($event['start_date'], $event['timezone']);
        $end = CarbonImmutable::parse($event['end_date'] ?? $event['start_date'], $event['timezone']);

        if ($start->isSameDay($end)) {
            return $start->format('F j, Y');
        }

        if ($start->year === $end->year && $start->month === $end->month) {
            return $start->format('F j').'–'.$end->format('j, Y');
        }

        if ($start->year === $end->year) {
            return $start->format('F j').' – '.$end->format('F j, Y');
        }

        return $start->format('F j, Y').' – '.$end->format('F j, Y');
    }

    /** @param array<string, mixed> $location */
    private function formatLocation(array $location): string
    {
        $physical = implode(', ', array_filter([
            $location['venue'],
            $location['address'],
            $location['city'],
            $location['country'],
        ], static fn (mixed $value): bool => is_string($value) && $value !== ''));

        return match ($location['type']) {
            'online' => $location['platform'],
            'hybrid' => $physical.' + '.$location['platform'],
            default => $physical,
        };
    }

    /**
     * @param  list<array<string, mixed>>  $itinerary
     * @return list<array<string, mixed>>
     */
    private function formatItinerary(array $itinerary, string $timezone): array
    {
        return array_map(fn (array $day): array => [
            ...$day,
            'presentation' => [
                'date' => CarbonImmutable::parse($day['date'], $timezone)->format('l, F j, Y'),
                'start_time' => $this->formatTime($day['start_time']),
                'end_time' => $this->formatTime($day['end_time']),
            ],
        ], $itinerary);
    }

    /** @param array<string, mixed> $event */
    private function formatApplicationDeadline(
        array $event,
        string $state,
        CarbonImmutable $now,
        bool $includePastDeadline,
    ): ?string {
        if ($event['application_deadline'] === null || ($state === 'past' && ! $includePastDeadline)) {
            return null;
        }

        $deadline = CarbonImmutable::parse($event['application_deadline'])
            ->setTimezone($event['timezone']);

        if ($deadline->greaterThan($now->setTimezone($event['timezone']))) {
            return $deadline->format('F j, Y \a\t g:i A');
        }

        return 'Applications closed';
    }
}

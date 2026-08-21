@props(['event'])

{{-- Format the validated event fields for the compact card presentation. --}}
@php
    $startDate = (new \DateTimeImmutable($event['start_date']))->format('d.m.Y');
    $endDate = isset($event['end_date'])
        ? (new \DateTimeImmutable($event['end_date']))->format('d.m.Y')
        : $startDate;
    $dateLabel = $startDate === $endDate ? $startDate : "{$startDate} - {$endDate}";
    $typeLabel = str($event['type'])->replace('-', ' ')->upper();
    $href = $event['type'] === 'meeting'
        ? $event['meeting_site_url']
        : "/events/{$event['slug']}";
    $physicalLocation = implode(', ', array_filter([
        $event['location']['venue'],
        $event['location']['city'],
        $event['location']['country'],
    ]));
    $locationLabel = match ($event['location']['type']) {
        'online' => $event['location']['platform'],
        'hybrid' => "{$physicalLocation} + {$event['location']['platform']}",
        default => $physicalLocation,
    };
@endphp

<article
    {{ $attributes->class([
        'card-hover-lift h-full w-full border border-line-soft bg-default',
    ]) }}
    data-event-card
>
    {{-- Keep every card detail inside one semantic destination link. --}}
    <a
        href="{{ $href }}"
        aria-label="View event: {{ $event['title'] }}"
        class="flex h-full min-h-[420px] flex-col p-5 focus-visible:outline-2 focus-visible:-outline-offset-2 focus-visible:outline-accent md:p-6 lg:min-h-[480px] lg:p-8"
    >
        <header class="flex flex-col gap-4">
            <p class="font-body text-sm leading-5 font-medium tracking-[0.04em] text-secondary">
                {{ $typeLabel }}
            </p>
            <div class="border-t border-line-soft" aria-hidden="true"></div>
        </header>

        {{-- Give the title and required summary the flexible middle area of equal-height cards. --}}
        <div class="flex flex-1 flex-col gap-4 py-6 lg:gap-5 lg:py-8">
            <h3 class="font-heading text-xl leading-7 font-medium text-primary lg:text-2xl lg:leading-8">
                {{ $event['title'] }}
            </h3>
            <p class="font-body text-sm leading-5 text-secondary lg:text-base lg:leading-6">
                {{ $event['summary'] }}
            </p>
        </div>

        {{-- Anchor the date and venue to the lower card edge as shown in the reference. --}}
        <footer class="flex flex-col gap-3 border-t border-line-soft pt-5 font-body">
            <time
                datetime="{{ $event['start_date'] }}"
                class="text-sm leading-5 italic text-secondary lg:text-base lg:leading-6"
            >
                {{ $dateLabel }}
            </time>
            <p class="text-sm leading-5 text-primary lg:text-base lg:leading-6">
                {{ $locationLabel }}
            </p>
        </footer>
    </a>
</article>

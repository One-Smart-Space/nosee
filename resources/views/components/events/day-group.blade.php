@props(['day'])

<section class="grid gap-4 lg:grid-cols-[304px_minmax(0,1fr)] lg:gap-8" data-event-day-group>
    <header class="border-line-mild lg:border-r lg:px-3 lg:py-8">
        <time
            datetime="{{ $day['date'] }}"
            class="block font-heading text-base leading-6 font-medium text-primary uppercase lg:text-center lg:text-[32px] lg:leading-9"
        >
            {{ $day['label'] }}
        </time>
    </header>

    <div class="flex min-w-0 flex-col gap-4 lg:gap-8">
        @foreach ($day['events'] as $event)
            <x-events.listing-event-card :event="$event" />
        @endforeach
    </div>
</section>

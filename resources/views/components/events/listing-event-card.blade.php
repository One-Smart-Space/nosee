@props(['event'])

@php
    $type = config('event-types.'.$event['type']);
    $usesDates = $event['type'] === 'meeting' || $event['schedule']['mode'] === 'multi_day';
    $target = $event['external'] ? '_blank' : null;
    $rel = $event['external'] ? 'noopener noreferrer' : null;
    $cta = $event['external'] ? 'Explore '.$event['title'] : 'View details';
    $deadline = $event['presentation']['application_deadline'];
    $deadlineClosed = $deadline === 'Applications closed';
@endphp

<article
    {{ $attributes->class('card-hover-lift grid overflow-hidden border border-line-mild bg-default lg:grid-cols-[191px_minmax(0,1fr)_191px]') }}
    data-listing-event-card
>
    <div @class([
        'flex flex-col items-center justify-center gap-3 border-b border-line-mild p-4 lg:row-span-1 lg:min-h-[184px] lg:border-r lg:border-b-0',
        $type['duration_classes'],
    ])>
        <img
            src="{{ $usesDates ? '/media/icons/CalendarDots.svg' : '/media/icons/Clock.svg' }}"
            alt=""
            @class(['size-8', 'brightness-0 invert' => $event['type'] === 'meeting'])
            aria-hidden="true"
        >

        @if ($usesDates)
            <div class="flex items-baseline justify-center gap-4">
                <time datetime="{{ $event['start_date'] }}" class="text-center">
                    <span class="block text-2xl leading-7 font-medium">{{ $event['presentation']['start_date']['day'] }}</span>
                    <span class="block text-sm leading-5">{{ $event['presentation']['start_date']['month'] }}</span>
                </time>
                @if ($event['end_date'])
                    <span class="text-xl" aria-hidden="true">–</span>
                    <time datetime="{{ $event['end_date'] }}" class="text-center">
                        <span class="block text-2xl leading-7 font-medium">{{ $event['presentation']['end_date']['day'] }}</span>
                        <span class="block text-sm leading-5">{{ $event['presentation']['end_date']['month'] }}</span>
                    </time>
                @endif
            </div>
        @else
            <div class="flex w-full items-center justify-between gap-4 px-1">
                <time datetime="{{ $event['schedule']['start_time'] }}" class="text-xl leading-7 font-medium">
                    {{ $event['presentation']['start_time'] }}
                </time>
                @if ($event['presentation']['end_time'])
                    <span class="text-secondary" aria-hidden="true">–</span>
                    <time datetime="{{ $event['schedule']['end_time'] }}" class="text-xl leading-7 font-medium">
                        {{ $event['presentation']['end_time'] }}
                    </time>
                @endif
            </div>
        @endif
    </div>

    <div class="flex min-w-0 flex-col justify-center gap-3 p-4 lg:min-h-[184px]">
        <p class="w-fit px-2 py-0.5 text-xs leading-5 font-medium uppercase {{ $type['tag_classes'] }}">
            {{ str($event['type'])->replace('-', ' ') }}
        </p>
        <h4 class="font-heading text-xl leading-7 font-medium text-primary lg:text-2xl lg:leading-8">
            <a
                href="{{ $event['destination'] }}"
                @if ($target) target="{{ $target }}" rel="{{ $rel }}" @endif
                class="rounded-sm focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-accent"
            >
                {{ $event['title'] }}
            </a>
        </h4>
        <p class="text-sm leading-5 text-secondary lg:text-base">{{ $event['summary'] }}</p>
    </div>

    <div class="flex items-center gap-4 border-t border-line-mild p-4 lg:flex-col lg:justify-center lg:border-t-0 lg:border-l">
        <p class="flex min-w-0 flex-1 items-start gap-1 text-sm leading-5 text-secondary lg:flex-none lg:text-center">
            <img src="/media/icons/MapPin.svg" alt="" class="mt-0.5 size-5 shrink-0" aria-hidden="true">
            <span>{{ $event['presentation']['location'] }}</span>
        </p>

        <x-ui.button
            :href="$event['destination']"
            size="sm"
            variant="stroke"
            icon-position="right"
            :target="$target"
            :rel="$rel"
            class="hidden max-w-full lg:inline-flex"
        >
            <span class="truncate">{{ $cta }}</span>
            <x-slot:icon>
                @if ($event['external'])
                    <span class="relative block size-4" aria-hidden="true">
                        <img src="/media/icons/arrow-up-right-stem.svg" alt="" class="absolute inset-1/4 size-2.5">
                        <img src="/media/icons/arrow-up-right-head.svg" alt="" class="absolute top-1/4 right-1/4 size-2">
                    </span>
                @else
                    <img src="/media/icons/arrow-right.svg" alt="" class="size-4">
                @endif
            </x-slot:icon>
        </x-ui.button>
    </div>

    @if ($deadline)
        <p
            @class([
                'border-t border-line-mild px-4 py-2 text-sm leading-5 lg:col-span-3',
                'font-medium text-secondary' => $deadlineClosed,
                'text-primary' => ! $deadlineClosed,
            ])
            data-application-deadline
        >
            @if ($deadlineClosed)
                {{ $deadline }}
            @else
                <span class="text-secondary">Apply before:</span>
                <time>{{ $deadline }}</time>
            @endif
        </p>
    @endif
</article>

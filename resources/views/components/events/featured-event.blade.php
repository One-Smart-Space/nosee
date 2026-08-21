@props(['event'])

@php
    $target = $event['external'] ? '_blank' : null;
    $rel = $event['external'] ? 'noopener noreferrer' : null;
    $cta = $event['external'] ? 'Explore '.$event['title'] : 'View details';
@endphp

<article
    {{ $attributes->class('card-hover-lift grid overflow-hidden border border-line-soft bg-default lg:grid-cols-[minmax(0,415px)_minmax(0,1fr)_191px]') }}
    data-featured-event
>
    <div class="min-h-52 bg-[#d9d9d9] lg:min-h-[308px]">
        @if ($event['image'])
            <img
                src="{{ $event['image'] }}"
                alt="{{ $event['image_alt'] }}"
                class="h-full w-full object-cover"
            >
        @endif
    </div>

    <div class="flex flex-col items-start justify-center gap-4 border-b border-line-soft p-5 sm:p-6 lg:border-r lg:border-b-0 lg:p-8">
        <p class="bg-surface px-3 py-1 text-sm leading-6 text-primary uppercase">Featured event</p>
        <h2 class="font-heading text-[28px] leading-9 font-medium text-primary">
            <a
                href="{{ $event['destination'] }}"
                @if ($target) target="{{ $target }}" rel="{{ $rel }}" @endif
                class="rounded-sm focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-accent"
            >
                {{ $event['title'] }}
            </a>
        </h2>
        <p class="font-body text-base leading-5 text-secondary">{{ $event['summary'] }}</p>

        <x-ui.button
            :href="$event['destination']"
            size="base"
            variant="stroke"
            icon-position="right"
            :target="$target"
            :rel="$rel"
            class="max-w-full"
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

    <div class="flex items-center gap-5 p-5 sm:p-6 lg:flex-col lg:justify-center lg:gap-8 lg:p-4">
        <div class="flex shrink-0 items-baseline gap-3 lg:w-full lg:justify-center">
            <time datetime="{{ $event['start_date'] }}" class="text-center">
                <span class="block font-heading text-[32px] leading-8 font-medium">{{ $event['presentation']['start_date']['day'] }}</span>
                <span class="block text-sm leading-5 text-secondary">{{ $event['presentation']['start_date']['month'] }}</span>
            </time>
            @if ($event['end_date'])
                <span class="text-2xl text-secondary" aria-hidden="true">–</span>
                <time datetime="{{ $event['end_date'] }}" class="text-center">
                    <span class="block font-heading text-[32px] leading-8 font-medium">{{ $event['presentation']['end_date']['day'] }}</span>
                    <span class="block text-sm leading-5 text-secondary">{{ $event['presentation']['end_date']['month'] }}</span>
                </time>
            @endif
        </div>

        <p class="flex min-w-0 items-start gap-1 text-sm leading-5 text-secondary lg:text-center">
            <img src="/media/icons/MapPin.svg" alt="" class="mt-0.5 size-5 shrink-0" aria-hidden="true">
            <span>{{ $event['presentation']['location'] }}</span>
        </p>
    </div>
</article>

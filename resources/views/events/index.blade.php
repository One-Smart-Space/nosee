@extends('layouts.app')

@section('title', 'Events | NOSEE')

@section('content')
    <div class="events-listing pb-20" data-events-page>
        <x-ui.container class="flex flex-col gap-10 pt-10 lg:gap-16 lg:pt-16">
            <header class="flex items-end justify-between gap-12">
                <div class="max-w-xl">
                    <h1 class="font-heading text-[40px] leading-[48px] font-semibold text-primary lg:text-5xl lg:leading-14">
                        Events
                    </h1>
                    <p class="mt-2 text-base leading-6 text-secondary lg:text-xl lg:leading-7">
                        Explore upcoming NOSEE meetings, conferences, workshops, lectures, and outreach events.
                    </p>
                </div>

                <form action="{{ route('events.index') }}" method="GET" class="hidden h-12 w-[416px] shrink-0 items-center border border-line-strong bg-default pr-1 pl-4 lg:flex" role="search">
                    <label for="event-search" class="sr-only">Search upcoming events</label>
                    <input
                        id="event-search"
                        name="q"
                        type="search"
                        value="{{ $query }}"
                        placeholder="Search events..."
                        class="min-w-0 flex-1 bg-transparent text-base text-primary outline-none placeholder:text-secondary"
                    >
                    <button type="submit" class="inline-flex h-10 items-center justify-center gap-1.5 bg-brand px-4 text-sm font-medium text-inverse focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-accent">
                        Search
                        <img src="/media/icons/search.svg" alt="" class="size-4" aria-hidden="true">
                    </button>
                </form>
            </header>

            @if ($featured)
                <x-events.featured-event :event="$featured" />
            @endif

            @if ($ongoing !== [])
                <section aria-labelledby="ongoing-events-heading" data-ongoing-events>
                    <header class="border-b border-line-soft py-4 lg:pt-6">
                        <h2 id="ongoing-events-heading" class="font-heading text-[28px] leading-8 font-medium lg:text-[32px] lg:leading-9">
                            Ongoing Events
                        </h2>
                    </header>
                    <div class="mt-5 flex flex-col gap-4 lg:mt-8 lg:gap-8 lg:pl-[336px]">
                        @foreach ($ongoing as $event)
                            <x-events.listing-event-card :event="$event" />
                        @endforeach
                    </div>
                </section>
            @endif

            <section aria-labelledby="upcoming-events-heading" data-upcoming-events>
                <header class="flex items-center justify-between gap-4 border-b border-line-soft py-4 lg:pt-6">
                    <h2 id="upcoming-events-heading" class="font-heading text-[28px] leading-8 font-medium lg:text-[32px] lg:leading-9">
                        Upcoming Events
                    </h2>
                    <x-ui.button :href="route('events.archive', absolute: false)" size="sm" variant="stroke" icon-position="right" class="hidden sm:inline-flex">
                        View event archives
                        <x-slot:icon>
                            <img src="/media/icons/arrow-right.svg" alt="" class="size-4">
                        </x-slot:icon>
                    </x-ui.button>
                </header>

                @if ($upcoming_groups === [])
                    <div class="border-b border-line-soft py-12 text-center" data-events-empty-state>
                        <p class="font-heading text-2xl leading-8 font-medium text-primary">No upcoming events found.</p>
                        @if ($query)
                            <p class="mt-2 text-base leading-6 text-secondary">Try a different search term.</p>
                        @endif
                    </div>
                @else
                    <div class="flex flex-col gap-16">
                        @foreach ($upcoming_groups as $group)
                            <section aria-labelledby="event-month-{{ $group['key'] }}" data-event-month>
                                <x-events.month-heading :label="$group['label']" id="event-month-{{ $group['key'] }}" />
                                <div class="mt-5 flex flex-col gap-10 lg:mt-8 lg:gap-8">
                                    @foreach ($group['days'] as $day)
                                        <x-events.day-group :day="$day" />
                                    @endforeach
                                </div>
                            </section>
                        @endforeach
                    </div>
                @endif
            </section>
        </x-ui.container>
    </div>
@endsection

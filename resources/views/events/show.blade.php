@extends('layouts.app')

@section('title', $event['title'].' | NOSEE')

@section('content')
    <article class="pb-20" data-event-detail-page data-event-state="{{ $event['state'] }}">
        <x-ui.container class="pt-10 lg:pt-16">
            <header class="max-w-5xl border-b border-line-soft pb-8 lg:pb-12">
                <p class="w-fit px-3 py-1 text-sm leading-6 font-medium uppercase {{ $event['presentation']['type']['tag_classes'] }}">
                    {{ str($event['type'])->replace('-', ' ') }}
                </p>
                <h1 class="mt-4 font-heading text-[40px] leading-[48px] font-semibold text-primary lg:text-[56px] lg:leading-[64px]">
                    {{ $event['title'] }}
                </h1>
                <p class="mt-4 max-w-3xl text-lg leading-7 text-secondary lg:text-xl lg:leading-8">
                    {{ $event['summary'] }}
                </p>
            </header>

            <section class="grid border-b border-line-soft sm:grid-cols-2 lg:grid-cols-4" aria-label="Event information">
                <div class="border-b border-line-soft py-6 sm:border-r sm:pr-6 lg:border-b-0">
                    <h2 class="text-sm leading-5 font-medium text-secondary uppercase">Date</h2>
                    <p class="mt-2 font-heading text-xl leading-7 font-medium text-primary">
                        {{ $event['presentation']['date_range'] }}
                    </p>
                </div>

                @if ($event['presentation']['start_time'])
                    <div class="border-b border-line-soft py-6 sm:pl-6 lg:border-r lg:border-b-0 lg:px-6">
                        <h2 class="text-sm leading-5 font-medium text-secondary uppercase">Time</h2>
                        <p class="mt-2 font-heading text-xl leading-7 font-medium text-primary">
                            {{ $event['presentation']['start_time'] }}
                            @if ($event['presentation']['end_time'])
                                – {{ $event['presentation']['end_time'] }}
                            @endif
                        </p>
                        <p class="mt-1 text-sm leading-5 text-secondary">{{ $event['timezone'] }}</p>
                    </div>
                @endif

                <div class="border-b border-line-soft py-6 sm:border-r sm:pr-6 lg:border-b-0 lg:px-6">
                    <h2 class="text-sm leading-5 font-medium text-secondary uppercase">Location</h2>
                    <p class="mt-2 text-base leading-6 text-primary">{{ $event['presentation']['location'] }}</p>
                </div>

                @if ($event['presentation']['application_deadline'])
                    <div class="py-6 sm:pl-6">
                        <h2 class="text-sm leading-5 font-medium text-secondary uppercase">Application</h2>
                        @if ($event['presentation']['application_deadline'] === 'Applications closed')
                            <p class="mt-2 font-heading text-xl leading-7 font-medium text-primary">Applications closed</p>
                        @else
                            <p class="mt-2 text-base leading-6 text-primary">
                                <span class="text-secondary">Apply before</span><br>
                                {{ $event['presentation']['application_deadline'] }}
                            </p>
                        @endif
                    </div>
                @endif
            </section>

            @if ($event['registration_url'])
                <div class="mt-8">
                    <x-ui.button
                        :href="$event['registration_url']"
                        size="lg"
                        target="_blank"
                        rel="noopener noreferrer"
                        icon-position="right"
                    >
                        REGISTER
                        <x-slot:icon>
                            <img src="/media/icons/arrow-up-right.svg" alt="" class="size-4 brightness-0 invert">
                        </x-slot:icon>
                    </x-ui.button>
                </div>
            @endif

            <div class="mt-12 grid gap-12 lg:mt-16 lg:grid-cols-[minmax(0,2fr)_minmax(18rem,1fr)] lg:gap-16">
                <div class="flex min-w-0 flex-col gap-14">
                    <section aria-labelledby="event-description-heading">
                        <h2 id="event-description-heading" class="font-heading text-[32px] leading-9 font-medium text-primary">About this event</h2>
                        <p class="mt-5 whitespace-pre-line text-base leading-7 text-secondary">{{ $event['body'] }}</p>
                    </section>

                    @if ($event['schedule']['mode'] === 'multi_day' && $event['presentation']['itinerary'] !== [])
                        <section aria-labelledby="event-itinerary-heading" data-event-itinerary>
                            <h2 id="event-itinerary-heading" class="border-b border-line-soft pb-4 font-heading text-[32px] leading-9 font-medium text-primary">
                                Itinerary
                            </h2>
                            <ol>
                                @foreach ($event['presentation']['itinerary'] as $day)
                                    <li class="grid gap-4 border-b border-line-soft py-6 sm:grid-cols-[12rem_minmax(0,1fr)]" data-itinerary-day>
                                        <div>
                                            <time datetime="{{ $day['date'] }}" class="font-heading text-lg leading-7 font-medium text-primary">
                                                {{ $day['presentation']['date'] }}
                                            </time>
                                            <p class="mt-1 text-sm leading-5 text-secondary">
                                                <time datetime="{{ $day['start_time'] }}">{{ $day['presentation']['start_time'] }}</time>
                                                @if ($day['presentation']['end_time'])
                                                    – <time datetime="{{ $day['end_time'] }}">{{ $day['presentation']['end_time'] }}</time>
                                                @endif
                                            </p>
                                        </div>
                                        @if ($day['title'] || $day['description'])
                                            <div>
                                                @if ($day['title'])
                                                    <h3 class="font-heading text-xl leading-7 font-medium text-primary">{{ $day['title'] }}</h3>
                                                @endif
                                                @if ($day['description'])
                                                    <p class="mt-2 text-base leading-6 text-secondary">{{ $day['description'] }}</p>
                                                @endif
                                            </div>
                                        @endif
                                    </li>
                                @endforeach
                            </ol>
                        </section>
                    @endif

                    @if ($event['resources'] !== [])
                        <section aria-labelledby="event-resources-heading">
                            <h2 id="event-resources-heading" class="font-heading text-[32px] leading-9 font-medium text-primary">Resources</h2>
                            <ul class="mt-5 divide-y divide-line-soft border-y border-line-soft">
                                @foreach ($event['resources'] as $resource)
                                    <li>
                                        <a
                                            href="{{ $resource['url'] }}"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            class="flex min-h-14 items-center justify-between gap-4 py-3 font-medium text-brand focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-accent"
                                        >
                                            {{ $resource['label'] }}
                                            <img src="/media/icons/arrow-up-right.svg" alt="" class="size-4 shrink-0" aria-hidden="true">
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </section>
                    @endif
                </div>

                <aside class="flex flex-col gap-10 border-t border-line-soft pt-8 lg:border-t-0 lg:border-l lg:pt-0 lg:pl-8" aria-label="Event people">
                    <section aria-labelledby="event-organiser-heading">
                        <h2 id="event-organiser-heading" class="text-sm leading-5 font-medium text-secondary uppercase">Organiser</h2>
                        <p class="mt-2 font-heading text-xl leading-7 font-medium text-primary">{{ $event['organiser'] }}</p>
                    </section>

                    @if ($event['speakers'] !== [])
                        <section aria-labelledby="event-speakers-heading">
                            <h2 id="event-speakers-heading" class="text-sm leading-5 font-medium text-secondary uppercase">Speakers</h2>
                            <ul class="mt-3 space-y-3">
                                @foreach ($event['speakers'] as $speaker)
                                    <li class="font-heading text-xl leading-7 font-medium text-primary">{{ $speaker }}</li>
                                @endforeach
                            </ul>
                        </section>
                    @endif
                </aside>
            </div>
        </x-ui.container>
    </article>
@endsection

@extends('layouts.app')

@section('title', 'Event Archives | NOSEE')

@section('content')
    <div class="pb-20" data-event-archive-page>
        <x-ui.container class="pt-10 lg:pt-16">
            <header class="max-w-2xl">
                <h1 class="font-heading text-[40px] leading-[48px] font-semibold text-primary lg:text-5xl lg:leading-14">
                    Event Archives
                </h1>
                <p class="mt-2 text-base leading-6 text-secondary lg:text-xl lg:leading-7">
                    Explore completed NOSEE meetings, conferences, workshops, lectures, and outreach events.
                </p>
            </header>

            @if ($archiveGroups === [])
                <p class="mt-10 border-y border-line-soft py-12 text-center font-heading text-2xl leading-8 font-medium text-primary lg:mt-16" data-archive-empty-state>
                    No archived events are available yet.
                </p>
            @else
                <div class="mt-10 flex flex-col gap-16 lg:mt-16 lg:gap-20">
                    @foreach ($archiveGroups as $year)
                        <section aria-labelledby="archive-year-{{ $year['year'] }}" data-archive-year>
                            <h2 id="archive-year-{{ $year['year'] }}" class="border-b border-line-soft pb-4 font-heading text-[32px] leading-9 font-semibold text-primary lg:text-[40px] lg:leading-[48px]">
                                {{ $year['year'] }}
                            </h2>

                            <div class="flex flex-col gap-14 lg:gap-16">
                                @foreach ($year['months'] as $month)
                                    <section aria-labelledby="archive-month-{{ $month['key'] }}" data-archive-month>
                                        <header class="border-b border-line-soft py-4 lg:pt-6">
                                            <h3 id="archive-month-{{ $month['key'] }}" class="font-heading text-[28px] leading-8 font-medium text-primary lg:text-[32px] lg:leading-9">
                                                {{ $month['label'] }}
                                            </h3>
                                        </header>

                                        <div class="mt-5 flex flex-col gap-10 lg:mt-8 lg:gap-8">
                                            @foreach ($month['days'] as $day)
                                                <x-events.day-group :day="$day" />
                                            @endforeach
                                        </div>
                                    </section>
                                @endforeach
                            </div>
                        </section>
                    @endforeach
                </div>
            @endif
        </x-ui.container>
    </div>
@endsection

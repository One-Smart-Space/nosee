@props(['events'])

<section
    class="bg-default py-8 lg:py-16"
    aria-labelledby="upcoming-events-heading"
    data-upcoming-events-section
    data-reveal-section
>
    <x-ui.container>
        <div class="flex flex-col gap-8 lg:gap-12">
            {{-- Pair the desktop action with the heading and centre the mobile heading. --}}
            <header class="flex items-center justify-center lg:justify-between">
                <h2
                    id="upcoming-events-heading"
                    class="text-center font-heading text-[28px] leading-8 font-semibold lg:text-left lg:text-5xl lg:leading-[56px]"
                    data-reveal-heading
                >
                    Upcoming Events
                </h2>

                <div class="hidden lg:block" data-upcoming-events-desktop-cta data-reveal-actions>
                    <x-ui.button href="/events" class="px-6 tracking-[0.06em]">
                        SEE ALL EVENTS
                    </x-ui.button>
                </div>
            </header>

            {{-- Stretch one mobile stack into three equal-height desktop columns. --}}
            <div class="grid grid-cols-1 items-stretch gap-5 lg:grid-cols-3 lg:gap-8" data-upcoming-event-cards data-reveal-group>
                @foreach ($events as $event)
                    <div class="h-full" data-reveal-item>
                        <x-cards.event-card :event="$event" />
                    </div>
                @endforeach
            </div>

            {{-- Keep the shared events action below the card stack on mobile. --}}
            <div class="flex justify-center lg:hidden" data-upcoming-events-mobile-cta data-reveal-actions>
                <x-ui.button href="/events" class="px-6 tracking-[0.06em]">
                    SEE ALL EVENTS
                </x-ui.button>
            </div>
        </div>
    </x-ui.container>
</section>

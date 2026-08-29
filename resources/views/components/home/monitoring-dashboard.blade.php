@props(['records'])

<section
    class="bg-surface py-10 lg:py-16"
    aria-labelledby="monitoring-dashboard-heading"
    data-monitoring-dashboard-section
    data-reveal-section
>
    <x-ui.container>
        <div class="flex flex-col items-center gap-6 lg:gap-12">
            {{-- Introduce the dashboard collection with the responsive Figma typography. --}}

            <header class="flex items-center justify-center lg:justify-between w-full">
                <h2
                    id="monitoring-dashboard-heading"
                    class="text-center font-heading text-xl leading-6 font-medium lg:text-[28px] lg:leading-10"
                    data-reveal-heading
                >
                    EARTH-SPACE ENVIRONMENT MONITORING DASHBOARD
                </h2>

                <div class="hidden lg:block" data-research-outputs-desktop-cta data-reveal-actions>
                    <x-ui.button href="/data" class="px-6 tracking-[0.06em]">
                        SEE ALL DATA
                    </x-ui.button>
                </div>
            </header>

            {{-- Stack cards on mobile and keep equal widths while lifting interactions on desktop. --}}
            <div class="flex w-full flex-col gap-4 lg:flex-row lg:items-start lg:gap-8" data-monitoring-dashboard-cards data-reveal-group>
                @foreach ($records as $record)
                    <div class="card-hover-lift w-full lg:min-w-0 lg:flex-1">
                        <div class="h-full" data-reveal-item>
                            <x-cards.monitoring-dashboard-card :record="$record" class="h-full w-full" />
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Link the section to the complete Data catalogue with the shared button primitive. --}}
            <div data-reveal-actions>
                <x-ui.button href="/data" class="px-6 tracking-[0.06em] block lg:hidden">
                    ALL DATA
                </x-ui.button>
            </div>
        </div>
    </x-ui.container>
</section>

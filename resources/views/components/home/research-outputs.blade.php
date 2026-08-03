@props(['records'])

<section
    class="bg-default py-8 lg:py-16"
    aria-labelledby="research-outputs-heading"
    data-research-outputs-section
    data-reveal-section
>
    <x-ui.container>
        <div class="flex flex-col gap-8 lg:gap-12">
            {{-- Pair the desktop action with the heading while keeping the mobile heading centred. --}}
            <header class="flex items-center justify-center lg:justify-between">
                <h2
                    id="research-outputs-heading"
                    class="text-center font-heading text-[28px] leading-8 font-semibold lg:text-left lg:text-5xl lg:leading-[56px]"
                    data-reveal-heading
                >
                    Research Outputs
                </h2>

                <div class="hidden lg:block" data-research-outputs-desktop-cta data-reveal-actions>
                    <x-ui.button href="/publications" class="px-6 tracking-[0.06em]">
                        SEE ALL OUTPUTS
                    </x-ui.button>
                </div>
            </header>

            {{-- Stack cards with mobile spacing and switch to three equal desktop columns. --}}
            <div class="grid grid-cols-1 gap-5 lg:grid-cols-3 lg:gap-8" data-research-output-cards data-reveal-group>
                @foreach ($records as $record)
                    <div class="h-full" data-reveal-item>
                        <x-cards.research-output-card :record="$record" />
                    </div>
                @endforeach
            </div>

            {{-- Place the shared publications action below the cards on mobile only. --}}
            <div class="flex justify-center lg:hidden" data-research-outputs-mobile-cta data-reveal-actions>
                <x-ui.button href="/publications" class="px-6 tracking-[0.06em]">
                    SEE ALL OUTPUTS
                </x-ui.button>
            </div>
        </div>
    </x-ui.container>
</section>

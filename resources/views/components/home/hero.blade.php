@props(['items'])

@php($itemCount = count($items))

<section
    class="relative h-[78svh] touch-pan-y overflow-hidden bg-primary text-inverse lg:h-[850px]"
    role="region"
    aria-roledescription="carousel"
    aria-label="Featured homepage content"
    data-homepage-motion
    data-home-hero
    data-hero-carousel
>
    {{-- Animate this neutral visual wrapper without taking over the carousel track transform. --}}
    <div class="absolute inset-0" data-hero-entrance-visual>
        <div class="absolute inset-0 flex h-full transition-transform duration-[400ms] ease-in-out motion-reduce:transition-none" data-hero-track>
            @foreach ($items as $item)
                <div
                    class="relative h-full w-full shrink-0 overflow-hidden"
                    aria-hidden="{{ $loop->first ? 'false' : 'true' }}"
                    data-hero-image-slide
                >
                    <img
                        src="{{ $item['image'] }}"
                        alt="{{ $item['image_alt'] }}"
                        width="1440"
                        height="850"
                        @if ($loop->first)
                            fetchpriority="high"
                        @else
                            loading="lazy"
                        @endif
                        class="absolute top-0 left-1/2 h-full w-[968px] max-w-none -translate-x-1/2 object-cover object-center lg:inset-0 lg:w-full lg:translate-x-0"
                    >
                </div>
            @endforeach
        </div>

        {{-- Keep the Figma overlay stationary across every image. --}}
        <div
            class="pointer-events-none absolute inset-0 bg-black/75"
            aria-hidden="true"
        ></div>
        {{-- <div --}}
            {{-- class="pointer-events-none absolute inset-0" --}}
            {{-- style="background-image: linear-gradient(90deg, rgb(0 0 0 / 0.4) 0%, rgb(0 0 0 / 0.4) 100%), linear-gradient(107.23deg, #010617 20.377%, rgb(88 69 24 / 0.437) 49.101%, rgb(138 106 24 / 0.11) 72.401%);" --}}
            {{-- aria-hidden="true" --}}
        {{-- ></div> --}}
    </div>

    {{-- Fade and blur complete content panels so each slide updates as one unit. --}}
    <x-ui.container class="relative h-full">
        @foreach ($items as $item)
            <article
                @class([
                    'absolute inset-0 transition-[opacity,filter] duration-[400ms] ease-in-out motion-reduce:transition-none motion-reduce:blur-none',
                    'opacity-100 blur-none' => $loop->first,
                    'pointer-events-none opacity-0 blur-sm' => ! $loop->first,
                ])
                role="group"
                aria-roledescription="slide"
                aria-label="Slide {{ $loop->iteration }} of {{ $itemCount }}"
                aria-hidden="{{ $loop->first ? 'false' : 'true' }}"
                @if (! $loop->first) inert @endif
                data-hero-content-slide
                data-slide-id="{{ $item['id'] }}"
            >
                <div class="absolute top-1/2 right-4 left-4 flex -translate-y-1/2 flex-col items-start gap-5 lg:right-auto lg:left-[120px] lg:mt-10 lg:w-[852px] lg:gap-0">
                    <p
                        class="rounded-[2px] bg-white/40 px-2.5 py-1.5 text-xs leading-4 text-primary uppercase"
                        @if ($loop->first) data-hero-entrance-eyebrow @endif
                    >
                        {{ $item['eyebrow'] }}
                    </p>

                    <div class="flex w-full flex-col gap-3 text-inverse lg:mt-1 lg:gap-4">
                        <h1
                            class="font-heading text-[28px] leading-8 font-semibold lg:text-[56px] lg:leading-[64px]"
                            @if ($loop->first) data-hero-entrance-title @endif
                        >
                            {{ $item['title'] }}
                        </h1>

                        <p
                            class="max-w-[525px] text-base lg:text-xl"
                            @if ($loop->first) data-hero-entrance-summary @endif
                        >
                            {{ $item['summary'] }}
                        </p>
                    </div>

                    <div class="lg:mt-8" @if ($loop->first) data-hero-entrance-cta @endif>
                        <x-ui.button
                            href="{{ $item['cta_url'] }}"
                            size="lg"
                            icon-position="right"
                            class="gap-10 px-6 tracking-[0.06em] uppercase"
                        >
                            {{ $item['cta_label'] }}

                            <x-slot:icon>
                                <x-ui.arrow-right />
                            </x-slot:icon>
                        </x-ui.button>
                    </div>
                </div>

                <p
                    class="absolute right-[120px] bottom-[75px] hidden max-w-[294px] text-right text-sm leading-[18px] lg:block"
                    @if ($loop->first) data-hero-entrance-credit @endif
                >
                    {{ $item['image_credit'] }}
                </p>
            </article>
        @endforeach
    </x-ui.container>

    {{-- A single item needs no controls; multiple items receive one native button each. --}}
    @if ($itemCount > 1)
        <x-ui.container class="pointer-events-none absolute inset-x-0 bottom-4 lg:bottom-8" data-hero-controls>
            <div class="pointer-events-auto flex gap-2 lg:ml-[88px] xl:ml-14" aria-label="Hero slides" data-hero-entrance-indicators>
                @foreach ($items as $item)
                    <button
                        type="button"
                        @class([
                            'size-3 rounded-full border border-inverse focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-accent',
                            'bg-inverse' => $loop->first,
                            'bg-transparent' => ! $loop->first,
                        ])
                        aria-label="Show slide {{ $loop->iteration }}: {{ $item['title'] }}"
                        aria-current="{{ $loop->first ? 'true' : 'false' }}"
                        data-hero-indicator
                        data-slide-index="{{ $loop->index }}"
                    ></button>
                @endforeach
            </div>
        </x-ui.container>
    @endif
</section>

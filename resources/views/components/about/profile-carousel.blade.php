@props([
    'id',
    'title',
    'profiles',
    'intro' => null,
])

@php($profileCount = count($profiles))

<section
    id="{{ $id }}"
    {{ $attributes->class('scroll-mt-32') }}
    role="region"
    aria-roledescription="carousel"
    aria-labelledby="{{ $id }}-heading"
    data-profile-carousel
>
    <header class="grid gap-6 sm:grid-cols-[minmax(0,1fr)_auto] sm:items-start">
        <div>
            <div class="flex items-center gap-3">
                <span class="h-2 w-10 shrink-0 bg-brand" aria-hidden="true"></span>
                <h2 id="{{ $id }}-heading" class="font-heading text-[30px] leading-9 font-semibold text-primary sm:text-[36px] sm:leading-[44px] xl:text-[40px] xl:leading-[48px]">
                    {{ $title }}
                </h2>
            </div>

            @if (! empty($intro))
                <p class="mt-5 max-w-3xl text-base leading-6 text-secondary sm:text-lg sm:leading-7">
                    {{ $intro }}
                </p>
            @endif
        </div>

        @if ($profileCount > 1)
            <div class="hidden items-center gap-1 sm:flex" aria-label="{{ $title }} carousel controls" data-profile-arrows>
                <x-ui.button
                    size="sm"
                    intent="secondary"
                    variant="text"
                    icon-position="only"
                    aria-label="Show previous {{ strtolower($title) }} profiles"
                    data-profile-previous
                >
                    <x-slot:icon>
                        <x-ui.arrow-right class="rotate-180" />
                    </x-slot:icon>
                </x-ui.button>

                <x-ui.button
                    size="sm"
                    intent="secondary"
                    variant="text"
                    icon-position="only"
                    aria-label="Show next {{ strtolower($title) }} profiles"
                    data-profile-next
                >
                    <x-slot:icon>
                        <x-ui.arrow-right />
                    </x-slot:icon>
                </x-ui.button>
            </div>
        @endif
    </header>

    @if ($profileCount > 0)
        <div class="mt-10 touch-pan-y overflow-hidden" data-profile-viewport>
            <div class="flex gap-4 transition-transform duration-[400ms] ease-in-out motion-reduce:transition-none lg:gap-8" data-profile-track>
                @foreach ($profiles as $profile)
                    <figure
                        class="w-full shrink-0 sm:w-[calc((100%-1rem)/2)] lg:w-[calc((100%-4rem)/3)]"
                        role="group"
                        aria-roledescription="slide"
                        aria-label="Profile {{ $loop->iteration }} of {{ $profileCount }}"
                        data-profile-slide
                    >
                        <img
                            src="{{ $profile['image'] }}"
                            alt="{{ $profile['image_alt'] }}"
                            width="400"
                            height="400"
                            loading="lazy"
                            class="aspect-square w-full object-cover"
                        >
                        <figcaption class="mt-3 border-t border-line-strong pt-3">
                            <h3 class="font-heading text-lg leading-6 font-semibold text-primary uppercase">{{ $profile['name'] }}</h3>
                            <p class="mt-1 text-sm leading-5 text-secondary">{{ $profile['title'] }}</p>
                        </figcaption>
                    </figure>
                @endforeach
            </div>
        </div>

        @if ($profileCount > 1)
            <div class="mt-8 flex justify-center gap-2" aria-label="{{ $title }} profile groups" data-profile-indicators>
                @foreach ($profiles as $profile)
                    <button
                        type="button"
                        @class([
                            'size-2 rounded-full focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-accent',
                            'bg-brand' => $loop->first,
                            'bg-disabled' => ! $loop->first,
                        ])
                        aria-label="Show profile {{ $loop->iteration }}: {{ $profile['name'] }}"
                        aria-current="{{ $loop->first ? 'true' : 'false' }}"
                        data-profile-indicator
                    ></button>
                @endforeach
            </div>
        @endif
    @endif
</section>

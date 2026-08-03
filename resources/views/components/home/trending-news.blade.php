@props(['articles'])

@php
    $featuredArticle = $articles[0];
    $compactArticles = array_slice($articles, 1);
@endphp

<section
    class="bg-surface py-10 md:py-16"
    aria-labelledby="trending-news-heading"
    data-trending-news-section
    data-reveal-section
>
    <x-ui.container>
        <div class="flex flex-col gap-8 md:gap-12">
            {{-- Keep the section title centred across the supplied mobile and desktop layouts. --}}
            <h2
                id="trending-news-heading"
                class="text-center font-heading text-[28px] leading-8 font-semibold md:text-5xl md:leading-[56px]"
                data-reveal-heading
            >
                What’s Trending
            </h2>

            {{-- Stack both groups through tablet, then pair equal-width squares on desktop. --}}
            <div
                class="flex flex-col gap-4 md:gap-8 xl:grid xl:grid-cols-2"
                data-trending-news-layout
                data-reveal-group
            >
                <div class="aspect-square" data-trending-news-featured data-reveal-item>
                    <x-cards.trending-news-card
                        :article="$featuredArticle"
                        variant="featured"
                        class="size-full"
                    />
                </div>

                <div
                    class="grid grid-cols-1 gap-4 md:grid-cols-2 md:gap-8"
                    data-trending-news-compact-grid
                >
                    @foreach ($compactArticles as $article)
                        <div data-reveal-item>
                            <x-cards.trending-news-card :article="$article" variant="compact" />
                        </div>
                    @endforeach

                    <div class="hidden aspect-square md:block" data-reveal-actions>
                        <a
                            href="/news"
                            aria-label="View all news"
                            class="flex size-full items-center justify-center bg-brand/10 font-heading text-2xl font-medium text-brand transition-colors hover:bg-brand hover:text-inverse focus-visible:bg-brand focus-visible:text-inverse focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-accent"
                            data-trending-news-view-more
                        >
                            VIEW MORE
                        </a>
                    </div>
                </div>
            </div>

            {{-- Replace the square tile with the standard page action on mobile. --}}
            <div class="flex justify-center md:hidden" data-trending-news-mobile-cta data-reveal-actions>
                <x-ui.button href="/news" class="px-6 tracking-[0.06em]">
                    VIEW ALL NEWS
                </x-ui.button>
            </div>
        </div>
    </x-ui.container>
</section>

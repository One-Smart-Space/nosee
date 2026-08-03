@props([
    'article',
    'variant',
])

{{-- Resolve presentation values once and reject unsupported card variants. --}}
@php
    if (! in_array($variant, ['featured', 'compact'], true)) {
        throw new \InvalidArgumentException("Unsupported trending news card variant [{$variant}].");
    }

    $categoryLabel = str($article['category'])->replace('-', ' ')->title();
    $publishedDate = (new \DateTimeImmutable($article['published_at']))->format('d/m/Y');
    $variantClasses = $variant === 'featured'
        ? 'md:col-span-2 md:row-span-2'
        : '';
    $titleClasses = $variant === 'featured'
        ? 'text-xl leading-7 md:text-[40px] md:leading-[48px]'
        : 'text-xl leading-7';
    $actionRowClasses = $variant === 'featured'
        ? 'justify-start md:justify-between'
        : 'justify-start';
    $topDateClasses = $variant === 'featured' ? 'md:hidden' : '';
@endphp

<article
    {{ $attributes->class([
        'group relative aspect-square min-w-0 overflow-hidden bg-primary',
        $variantClasses,
    ]) }}
    data-trending-news-card
    data-variant="{{ $variant }}"
    data-mobile-variant="compact"
>
    {{-- Keep the full visual card as one keyboard-accessible article link. --}}
    <a
        href="/news/{{ $article['slug'] }}"
        aria-label="Read more: {{ $article['title'] }}"
        class="relative block size-full text-inverse focus-visible:outline-2 focus-visible:-outline-offset-2 focus-visible:outline-accent"
    >
        <img
            src="{{ $article['image'] }}"
            alt="{{ $article['image_alt'] }}"
            width="642"
            height="642"
            class="absolute inset-0 size-full object-cover origin-center transition-transform duration-300 group-hover:scale-[1.1] motion-reduce:transition-none motion-reduce:group-hover:scale-100"
        >
        <span class="absolute inset-0 bg-linear-to-t from-black/85 via-black/25 to-black/10" aria-hidden="true"></span>

        {{-- Preserve required editorial context that is not visibly shown in the reference cards. --}}
        <span class="sr-only">{{ $categoryLabel }}. {{ $article['excerpt'] }}</span>

        <time
            datetime="{{ $article['published_at'] }}"
            class="absolute top-4 right-4 bg-brand/70 px-3 py-2 font-body text-sm leading-5 font-medium md:top-5 md:right-5 {{ $topDateClasses }}"
        >
            {{ $publishedDate }}
        </time>

        {{-- Anchor the headline and read action to the lower edge on both card sizes. --}}
        <span class="absolute inset-x-0 bottom-0 flex flex-col gap-5 p-5 md:p-6">
            <span class="font-heading font-medium {{ $titleClasses }}">
                {{ $article['title'] }}
            </span>

            <span class="flex items-center gap-4 font-body text-base leading-6 font-medium {{ $actionRowClasses }}">
                @if ($variant === 'featured')
                    <time
                        datetime="{{ $article['published_at'] }}"
                        class="hidden bg-brand/70 px-3 py-2 text-sm leading-5 md:block"
                    >
                        {{ $publishedDate }}
                    </time>
                @endif

                <span class="inline-flex items-center gap-4">
                    Read more
                    <x-ui.arrow-right />
                </span>
            </span>
        </span>
    </a>
</article>

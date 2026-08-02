@props(['record'])

<article
    {{ $attributes->class('overflow-hidden border border-line-soft bg-default') }}
    data-monitoring-dashboard-card
>
    {{-- Keep the dashboard title compact while allowing long names to wrap. --}}
    <div class="p-2">
        <h3 class="break-words font-heading text-base leading-6 font-medium tracking-[-0.02em] text-[#0b5025]">
            {{ $record['title'] }}
        </h3>
    </div>

    {{-- Preserve a stable preview area regardless of the source image dimensions. --}}
    <div class="aspect-video overflow-hidden border-y border-line-soft">
        <img
            src="{{ $record['image'] }}"
            alt="{{ $record['image_alt'] }}"
            width="600"
            height="338"
            class="size-full object-cover"
        >
    </div>

    {{-- Pair the external product action with attribution that can wrap on narrow cards. --}}
    <div class="flex flex-wrap items-center justify-between gap-x-2 gap-y-1 p-2 text-xs tracking-[-0.02em]">
        <a
            href="{{ $record['source_url'] }}"
            target="_blank"
            rel="noopener noreferrer"
            aria-label="View full product: {{ $record['title'] }}"
            class="shrink-0 bg-brand px-2 py-1 leading-4 text-inverse focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-accent"
        >
            Full product
        </a>

        <p class="min-w-0 flex-1 break-words text-right text-secondary">
            Provided by: {{ $record['provider'] }}
        </p>
    </div>
</article>

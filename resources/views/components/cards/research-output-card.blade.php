@props(['record'])

{{-- Prepare display-only values from the validated publication record. --}}
@php
    $doiUrl = 'https://doi.org/'.$record['doi'];
    $publicationYear = substr($record['publication_date'], 0, 4);
    $researchAreaLabel = str($record['research_area'])->replace('-', ' ')->upper();
@endphp

<article
    {{ $attributes->class('flex w-full min-w-0 flex-col gap-4 border border-line-soft p-4 lg:flex-1 lg:gap-6 lg:border-0 lg:p-0') }}
    data-research-output-card
>
    {{-- Separate the research-area label from the publication details. --}}
    <header class="flex flex-col gap-3">
        <p class="font-body text-sm leading-5 tracking-[0.02em] text-brand lg:text-lg lg:leading-6">
            {{ $researchAreaLabel }}
        </p>
        <div class="border-t border-line-mild" aria-hidden="true"></div>
    </header>

    {{-- Keep the title and publication metadata compact on mobile and spacious on desktop. --}}
    <div class="flex flex-col gap-5 lg:gap-8">
        <h3 class="font-body text-lg leading-6 font-medium text-primary lg:text-2xl lg:leading-8 lg:font-normal">
            {{ $record['title'] }}
        </h3>

        <div class="flex flex-col gap-3 text-sm leading-5 lg:gap-4 lg:text-lg lg:leading-6">
            <p class="text-primary">{{ implode(', ', $record['authors']) }}</p>
            <p class="italic text-secondary">{{ $publicationYear }}, {{ $record['publication_source'] }}</p>
        </div>
    </div>

    {{-- Figma reserves the DOI action for desktop cards. --}}
    <div class="hidden lg:block">
        <x-ui.button
            :href="$doiUrl"
            intent="primary"
            variant="stroke"
            icon-position="right"
            target="_blank"
            rel="noopener noreferrer"
            :aria-label="'Read publication: '.$record['title']"
            class="gap-10 pl-5 pr-4 tracking-[-0.02em]"
        >
            Read Here

            <x-slot:icon>
                <x-ui.arrow-right />
            </x-slot:icon>
        </x-ui.button>
    </div>
</article>

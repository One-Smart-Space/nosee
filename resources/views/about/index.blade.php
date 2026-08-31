@extends('layouts.app')

@section('title', $about['page_title'].' | NOSEE')

@section('content')
    <article class="pb-24 lg:pb-32" data-about-page>
        <x-ui.container class="pt-[84px] lg:pt-20">
            <header class="grid gap-y-7 lg:grid-cols-8 lg:gap-x-6 lg:gap-y-0 xl:grid-cols-12 xl:gap-x-8">
                <h1 class="font-heading text-[22px] leading-[30px] font-medium text-primary lg:col-span-2 lg:pt-[13px] xl:col-span-3 xl:pt-0 xl:text-2xl xl:leading-8">
                    {{ $about['page_title'] }}
                </h1>
                <p class="font-heading text-[36px] leading-[42px] font-semibold text-primary lg:col-start-3 lg:col-span-6 lg:text-[46px] lg:leading-[54px] xl:col-start-5 xl:col-span-8 xl:text-[56px] xl:leading-[64px]">
                    {{ $about['intro']['headline'] }}
                </p>
            </header>

            <div class="mt-[38px] grid items-start gap-y-[34px] lg:mt-[74px] lg:grid-cols-8 lg:gap-x-6 lg:gap-y-0 xl:mt-16 xl:grid-cols-12 xl:gap-x-8">
                <picture class="block w-full lg:col-span-5 xl:col-span-8">
                    <source media="(min-width: 1280px)" srcset="{{ $about['intro']['image'] }}">
                    <source media="(min-width: 1024px)" srcset="{{ $about['intro']['image_tablet'] }}">
                    <img
                        src="{{ $about['intro']['image_mobile'] }}"
                        alt="{{ $about['intro']['image_alt'] }}"
                        width="370"
                        height="260"
                        fetchpriority="high"
                        class="h-[260px] w-full object-cover sm:h-[360px] xl:h-[400px]"
                    >
                </picture>

                <div class="lg:col-start-6 lg:col-span-3 xl:col-start-10">
                    <p class="text-[11px] leading-4 font-medium text-brand uppercase lg:text-xs lg:leading-[18px] xl:text-sm">
                        {{ $about['intro']['eyebrow'] }}
                    </p>
                    <p class="mt-[18px] text-base leading-[25px] text-secondary lg:mt-5 lg:text-[17px] lg:leading-[26px] xl:mt-6 xl:text-lg xl:leading-7">
                        <span class="lg:hidden">{{ $about['intro']['description_mobile'] }}</span>
                        <span class="hidden lg:inline xl:hidden">{{ $about['intro']['description_tablet'] }}</span>
                        <span class="hidden xl:inline">{{ $about['intro']['description'] }}</span>
                    </p>
                    <div class="mt-[51px] border-t border-line-mild pt-5 lg:mt-[37px] xl:mt-3 xl:pt-3">
                        <p class="text-[15px] leading-[22px] font-medium text-primary xl:text-base xl:leading-6">
                            <span class="xl:hidden">{{ $about['intro']['closing_statement_compact'] }}</span>
                            <span class="hidden xl:inline">{{ $about['intro']['closing_statement'] }}</span>
                        </p>
                    </div>
                </div>
            </div>

            <section id="mission" class="mt-[118px] scroll-mt-32 lg:mt-[156px] xl:mt-[120px]" aria-labelledby="mission-heading">
                <div class="grid gap-y-[108px] lg:grid-cols-8 lg:gap-x-6 lg:gap-y-0 xl:grid-cols-12 xl:gap-x-8">
                    <h2 id="mission-heading" class="font-heading text-[30px] leading-9 font-semibold text-primary lg:col-span-3 lg:max-w-[10rem] lg:text-[34px] lg:leading-[42px] xl:col-span-4 xl:text-[40px] xl:leading-[48px]">
                        {{ $about['mission']['title'] }}
                    </h2>
                    <p class="font-heading text-xl leading-[30px] font-medium text-primary lg:col-start-4 lg:col-span-5 lg:text-2xl lg:leading-[34px] xl:col-start-5 xl:col-span-7 xl:text-[28px] xl:leading-10">
                        <span class="lg:hidden">{{ $about['mission']['statement_mobile'] }}</span>
                        <span class="hidden lg:inline xl:hidden">{{ $about['mission']['statement_tablet'] }}</span>
                        <span class="hidden xl:inline">{{ $about['mission']['statement'] }}</span>
                    </p>
                </div>

                <ol class="mt-[87px] grid gap-y-[76px] lg:mt-[118px] lg:grid-cols-8 lg:gap-x-6 lg:gap-y-[60px] xl:mt-[120px] xl:grid-cols-12 xl:gap-x-8 xl:gap-y-0">
                    @foreach ($about['mission']['objectives'] as $objective)
                        <li @class([
                            'grid grid-cols-[3rem_1fr] gap-x-4 border-t border-line-mild pt-5 lg:col-span-4 lg:grid-cols-[3.75rem_1fr] lg:gap-x-6 lg:pt-[21px] xl:col-span-4 xl:grid-cols-[5rem_1fr] xl:gap-x-8 xl:border-line-strong xl:pt-4',
                            'lg:col-span-8 xl:col-span-4' => $loop->last,
                        ])>
                            <span class="text-base leading-[22px] font-medium text-brand xl:text-lg xl:leading-6">{{ $objective['number'] }}</span>
                            <div @class([
                                'lg:grid lg:grid-cols-[21.5625rem_1fr] lg:gap-x-[63px] xl:block' => $loop->last,
                            ])>
                                <h3 class="font-heading text-lg leading-[25px] font-medium text-primary lg:leading-[26px] xl:text-xl xl:leading-7">{{ $objective['title'] }}</h3>
                                <p @class([
                                    'mt-[11px] text-[15px] leading-[23px] text-secondary',
                                    'lg:mt-0 xl:mt-4 xl:text-base xl:leading-6' => $loop->last,
                                    'lg:mt-3 xl:mt-4 xl:text-base xl:leading-6' => ! $loop->last,
                                ])>
                                    <span class="lg:hidden">{{ $objective['description_mobile'] }}</span>
                                    <span class="hidden lg:inline">{{ $objective['description'] }}</span>
                                </p>
                            </div>
                        </li>
                    @endforeach
                </ol>
            </section>

            <blockquote class="mt-[110px] h-[390px] overflow-hidden bg-brand text-inverse lg:mt-[132px] lg:grid lg:h-[320px] lg:grid-cols-[1fr_18.5rem] xl:mt-[120px] xl:h-[330px] xl:grid-cols-[1fr_26rem]">
                <div class="px-6 pt-5 lg:grid lg:grid-cols-[3.5rem_1fr] lg:gap-x-6 lg:px-8 lg:pt-[18px] xl:grid-cols-[5rem_1fr] xl:gap-x-8 xl:pt-[22px]">
                    <span class="block font-heading text-[72px] leading-[72px] font-medium lg:text-[80px] lg:leading-[80px] xl:text-8xl xl:leading-[96px]" aria-hidden="true">&ldquo;</span>
                    <div class="mt-2 lg:mt-0 lg:pt-[42px] xl:pt-[38px]">
                        <p class="font-heading text-[28px] leading-9 font-medium lg:text-[34px] lg:leading-[44px] xl:text-[40px] xl:leading-[50px]">
                            {{ $about['quote']['text'] }}
                        </p>
                        <cite class="mt-2 block text-sm leading-5 font-medium not-italic lg:mt-3 xl:mt-1">
                            <span aria-hidden="true">- </span><span class="italic">{{ $about['quote']['attribution'] }}</span>
                        </cite>
                    </div>
                </div>
                <picture class="hidden h-full lg:block">
                    <source media="(min-width: 1280px)" srcset="{{ $about['quote']['image'] }}">
                    <img
                        src="{{ $about['quote']['image_tablet'] }}"
                        alt="{{ $about['quote']['image_alt'] }}"
                        width="296"
                        height="320"
                        loading="lazy"
                        class="h-full w-full object-cover"
                    >
                </picture>
            </blockquote>

            <section class="mt-[184px] lg:mt-[61px] xl:mt-[120px]" aria-labelledby="story-heading">
                <h2 id="story-heading" class="font-heading text-[30px] leading-9 font-semibold text-primary lg:text-[40px] lg:leading-[48px]">
                    {{ $about['story']['title'] }}
                </h2>
                <div class="mt-20 grid items-start gap-y-[68px] lg:mt-10 lg:grid-cols-8 lg:gap-x-6 lg:gap-y-0 xl:grid-cols-12 xl:gap-x-8">
                    <img
                        src="{{ $about['story']['image'] }}"
                        alt="{{ $about['story']['image_alt'] }}"
                        width="752"
                        height="500"
                        loading="lazy"
                        class="h-[300px] w-full object-cover lg:col-span-4 lg:h-[430px] xl:col-span-7 xl:h-[500px]"
                    >
                    <div class="lg:col-start-5 lg:col-span-4 xl:col-start-9">
                        <p class="font-heading text-2xl leading-[34px] font-medium text-primary">
                            {{ $about['story']['lead'] }}
                        </p>
                        <p class="mt-[10px] text-base leading-[26px] text-secondary lg:mt-4">
                            {{ $about['story']['body'] }}
                        </p>
                    </div>
                </div>
            </section>

            <x-about.profile-carousel
                id="leadership"
                :title="$about['leadership']['title']"
                :intro="$about['leadership']['intro']"
                :profiles="$about['leadership']['people']"
                class="mt-24 xl:mt-[120px]"
            />

            <section id="collaborations" class="mt-24 scroll-mt-32 border-t border-line-strong pt-8 xl:mt-36 xl:grid xl:grid-cols-12 xl:gap-x-8 xl:pt-10" aria-labelledby="collaboration-heading">
                <p class="text-sm leading-[18px] font-medium text-brand uppercase xl:col-span-3">
                    {{ $about['collaboration']['eyebrow'] }}
                </p>
                <div class="mt-6 xl:col-start-5 xl:col-span-8 xl:mt-0">
                    <h2 id="collaboration-heading" class="font-heading text-[36px] leading-[44px] font-semibold text-primary xl:text-[40px] xl:leading-[48px]">
                        {{ $about['collaboration']['title'] }}
                    </h2>
                    <p class="mt-6 max-w-2xl text-lg leading-7 text-secondary">
                        {{ $about['collaboration']['description'] }}
                    </p>
                    <nav class="mt-8 flex flex-wrap gap-x-8 gap-y-4" aria-label="Collaboration actions">
                        @foreach ($about['collaboration']['actions'] as $action)
                            <a href="{{ $action['url'] }}" class="border-b border-line-strong pb-1 text-base leading-6 font-medium text-primary transition-colors hover:border-brand hover:text-brand focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-accent">
                                {{ $action['label'] }}
                            </a>
                        @endforeach
                    </nav>
                </div>
            </section>
        </x-ui.container>
    </article>
@endsection

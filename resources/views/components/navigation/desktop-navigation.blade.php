<header
    class="fixed inset-x-0 top-0 z-40 hidden lg:block"
    data-desktop-navigation
    data-scroll-navigation
    data-transparent="{{ $transparent ? 'true' : 'false' }}"
>
    {{-- Full two-tier header used at the top of transparent pages. --}}
    <div data-navigation-state="transparent" @if (! $transparent) hidden @endif>
        <x-ui.container class="flex h-[10.25rem] items-center border-b border-white/70 text-inverse">
            <a
                href="/"
                @if ($homeCurrent) aria-current="page" @endif
                class="shrink-0 rounded-sm focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-accent"
            >
                <img
                    src="/logoWhite.png"
                    alt="{{ config('app.name', 'NOSEE') }}"
                    class="h-auto w-72 xl:w-[23.3125rem]"
                >
            </a>

            <div class="flex min-w-0 flex-1 flex-col self-stretch pl-8 xl:pl-16">
                <nav class="flex h-[6.25rem] items-end justify-end gap-1 pb-5 font-body" aria-label="Utility navigation">
                    @foreach ($utility as $item)
                        @if ($item['label'] === 'Support NSEE')
                            <x-ui.button
                                href="{{ $item['url'] }}"
                                size="sm"
                                :aria-current="$item['current'] ? 'page' : false"
                                class="ml-1 border-line-soft uppercase {{ $item['active'] ? 'underline decoration-2 decoration-white underline-offset-4' : '' }}"
                            >
                                {{ $item['label'] }}
                            </x-ui.button>
                        @else
                            <a
                                href="{{ $item['url'] }}"
                                @if ($item['current']) aria-current="page" @endif
                                @class([
                                    'rounded-sm px-5 py-3 text-base font-medium text-inverse transition-opacity hover:opacity-80 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-accent',
                                    'underline decoration-2 decoration-white underline-offset-4' => $item['active'],
                                ])
                            >
                                {{ $item['label'] }}
                            </a>
                        @endif
                    @endforeach
                </nav>

                <nav class="h-16" aria-label="Primary navigation">
                    <x-navigation.desktop-primary-links :items="$primary" />
                </nav>
            </div>
        </x-ui.container>
    </div>

    {{-- Compact header used by default and after transparent pages are scrolled. --}}
    <div
        data-navigation-state="compact"
        class="bg-[rgb(19_133_61_/_0.5)] text-inverse backdrop-blur-[10px]"
        @if ($transparent) hidden @endif
    >
        <x-ui.container class="flex h-16 items-center">
            <a
                href="/"
                @if ($homeCurrent) aria-current="page" @endif
                class="shrink-0 rounded-sm focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-accent"
            >
                <img
                    src="/logo.png"
                    alt="{{ config('app.name', 'NOSEE') }}"
                    class="h-auto w-36"
                >
            </a>

            <nav class="ml-auto h-full min-w-0 flex-1 pl-8 xl:pl-16" aria-label="Primary navigation">
                <x-navigation.desktop-primary-links :items="$primary" />
            </nav>
        </x-ui.container>
    </div>
</header>

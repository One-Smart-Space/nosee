<header
    class="group/navigation fixed inset-x-0 top-0 z-40 hidden bg-black/70 text-inverse backdrop-blur-[10px] lg:block"
    data-desktop-navigation
    data-scroll-navigation
    data-transparent="{{ $transparent ? 'true' : 'false' }}"
    data-navigation-mode="{{ $transparent ? 'expanded' : 'compact' }}"
>
    <x-ui.container
        class="flex h-[10.25rem] items-center transition-[height] duration-300 ease-out motion-reduce:transition-none group-data-[navigation-mode=compact]/navigation:h-[6.5rem]"
    >
        <a
            href="/"
            @if ($homeCurrent) aria-current="page" @endif
            class="shrink-0 rounded-sm focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-accent"
        >
            <img
                src="/logoWhite.png"
                alt="{{ config('app.name', 'NOSEE') }}"
                class="h-auto w-72 transition-[width] duration-300 ease-out motion-reduce:transition-none group-data-[navigation-mode=compact]/navigation:w-56 xl:w-[23.3125rem] xl:group-data-[navigation-mode=compact]/navigation:w-56"
            >
        </a>

        <div class="flex min-w-0 flex-1 flex-col self-stretch pl-8 xl:pl-16">
            <nav
                class="flex h-[6.25rem] items-end justify-end gap-1 overflow-hidden pb-5 font-body transition-[height,padding] duration-300 ease-out motion-reduce:transition-none group-data-[navigation-mode=compact]/navigation:h-12 group-data-[navigation-mode=compact]/navigation:pb-2"
                aria-label="Utility navigation"
            >
                @foreach ($utility as $item)
                    @if ($item['label'] === 'Support NOSEE')
                        <x-ui.button
                            href="{{ $item['url'] }}"
                            size="sm"
                            :aria-current="$item['current'] ? 'page' : false"
                            class="ml-1 border-line-soft uppercase {{ $item['active'] ? 'underline decoration-2 decoration-white underline-offset-4' : '' }}"
                        >
                            {{ $item['label'] }}
                        </x-ui.button>
                    @elseif ($item['label'] === 'Login')
                        <span class="mx-3 h-8 w-px shrink-0 bg-white" aria-hidden="true"></span>
                        <a
                            href="{{ $item['url'] }}"
                            aria-label="Login"
                            @if ($item['current']) aria-current="page" @endif
                            class="flex size-9 shrink-0 items-center justify-center rounded-sm text-inverse transition-opacity hover:opacity-80 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-accent"
                        >
                            <svg
                                class="size-7"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.75"
                                aria-hidden="true"
                            >
                                <circle cx="12" cy="7" r="4" />
                                <path d="M4.5 21a7.5 7.5 0 0 1 15 0" stroke-linecap="round" />
                            </svg>
                        </a>
                    @else
                        <a
                            href="{{ $item['url'] }}"
                            @if ($item['current']) aria-current="page" @endif
                            @class([
                                'inline-flex h-9 items-center rounded-sm px-5 text-base font-medium text-inverse transition-opacity hover:opacity-80 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-accent',
                                'underline decoration-2 decoration-white underline-offset-4' => $item['active'],
                            ])
                        >
                            {{ $item['label'] }}
                        </a>
                    @endif
                @endforeach
            </nav>

            <nav
                class="h-16 transition-[height] duration-300 ease-out motion-reduce:transition-none group-data-[navigation-mode=compact]/navigation:h-14"
                aria-label="Primary navigation"
            >
                <x-navigation.desktop-primary-links :items="$primary" />
            </nav>
        </div>
    </x-ui.container>
</header>

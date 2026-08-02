<header
    class="fixed inset-x-0 top-0 z-40 lg:hidden"
    data-mobile-navigation
    data-scroll-navigation
    data-transparent="{{ $transparent ? 'true' : 'false' }}"
>
    {{-- Stack both header states so shared scroll logic can switch them without rebuilding markup. --}}
    <div class="relative z-20 h-[4.5rem] text-inverse">
        <div class="absolute inset-0" data-navigation-state="transparent" @if (! $transparent) hidden @endif>
            <x-ui.container class="h-full">
                <div class="flex h-full items-center border-b border-white/70">
                    <a
                        href="/"
                        @if ($homeCurrent) aria-current="page" @endif
                        class="rounded-sm focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-accent"
                    >
                        <img src="/logoWhite.png" alt="{{ config('app.name', 'NOSEE') }}" class="h-auto w-36">
                    </a>
                </div>
            </x-ui.container>
        </div>

        <div
            class="absolute inset-0 bg-[rgb(19_133_61_/_0.5)] backdrop-blur-[10px]"
            data-navigation-state="compact"
            @if ($transparent) hidden @endif
        >
            <x-ui.container class="flex h-full items-center">
                <a
                    href="/"
                    @if ($homeCurrent) aria-current="page" @endif
                    class="rounded-sm focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-accent"
                >
                    <img src="/logo.png" alt="{{ config('app.name', 'NOSEE') }}" class="h-auto w-36">
                </a>
            </x-ui.container>
        </div>

        {{-- Keep one accessible control responsible for opening and closing the drawer. --}}
        <x-ui.container class="pointer-events-none absolute inset-0 flex h-full items-center justify-end">
            <button
                type="button"
                class="pointer-events-auto flex size-11 items-center justify-center rounded-md text-inverse focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-accent"
                aria-label="Open navigation menu"
                aria-expanded="false"
                aria-controls="mobile-navigation-drawer"
                data-mobile-menu-button
            >
                <span class="relative block size-8" aria-hidden="true" data-mobile-menu-open-icon>
                    <span class="absolute top-[0.4375rem] left-1/2 h-0.5 w-[1.375rem] -translate-x-1/2 bg-current"></span>
                    <span class="absolute top-1/2 left-1/2 h-0.5 w-[1.375rem] -translate-1/2 bg-current"></span>
                    <span class="absolute bottom-[0.4375rem] left-1/2 h-0.5 w-[1.375rem] -translate-x-1/2 bg-current"></span>
                </span>
                <span class="relative hidden size-8" aria-hidden="true" data-mobile-menu-close-icon>
                    <span class="absolute top-1/2 left-1/2 h-0.5 w-[1.375rem] -translate-1/2 rotate-45 bg-current"></span>
                    <span class="absolute top-1/2 left-1/2 h-0.5 w-[1.375rem] -translate-1/2 -rotate-45 bg-current"></span>
                </span>
            </button>
        </x-ui.container>
    </div>

    {{-- The overlay closes the drawer while separating it visually from page content. --}}
    <button
        type="button"
        class="fixed inset-x-0 top-[4.5rem] bottom-0 z-0 bg-black/60"
        aria-label="Close navigation menu"
        tabindex="-1"
        data-mobile-overlay
        hidden
    ></button>

    {{-- The modal drawer contains primary links, expandable submenus, and utility actions. --}}
    <div
        id="mobile-navigation-drawer"
        class="fixed inset-x-0 top-[4.5rem] z-10 max-h-[calc(100dvh-4.5rem)] overflow-y-auto bg-default text-primary shadow-xl"
        role="dialog"
        aria-modal="true"
        aria-label="Navigation menu"
        data-mobile-drawer
        hidden
    >
        <x-ui.container class="py-5">
            <nav aria-label="Mobile navigation">
                <ul>
                    @foreach ($primary as $item)
                        @php($submenuId = 'mobile-submenu-'.$loop->index)

                        <li class="border-b border-line-soft">
                            <div class="flex min-h-11 items-stretch">
                                <a
                                    href="{{ $item['url'] }}"
                                    @if ($item['current']) aria-current="page" @endif
                                    @class([
                                        'flex min-h-11 flex-1 items-center rounded-sm font-heading text-base font-semibold focus-visible:outline-2 focus-visible:outline-offset-1 focus-visible:outline-accent',
                                        'text-brand' => $item['active'],
                                    ])
                                >
                                    {{ $item['label'] }}
                                </a>

                                @if (isset($item['children']))
                                    <button
                                        type="button"
                                        class="group flex size-11 shrink-0 items-center justify-center rounded-md focus-visible:outline-2 focus-visible:outline-offset-1 focus-visible:outline-accent"
                                        aria-label="Toggle {{ $item['label'] }} submenu"
                                        aria-expanded="false"
                                        aria-controls="{{ $submenuId }}"
                                        data-mobile-submenu-button
                                        @if ($item['active']) data-mobile-active-submenu @endif
                                    >
                                        <svg
                                            class="size-5 transition-transform group-aria-expanded:rotate-180"
                                            viewBox="0 0 20 20"
                                            fill="none"
                                            stroke="currentColor"
                                            stroke-width="1.75"
                                            aria-hidden="true"
                                        >
                                            <path d="m5 7.5 5 5 5-5" stroke-linecap="round" stroke-linejoin="round" />
                                        </svg>
                                    </button>
                                @endif
                            </div>

                            @if (isset($item['children']))
                                <ul id="{{ $submenuId }}" class="pb-3 pl-4" data-mobile-submenu hidden>
                                    @foreach ($item['children'] as $child)
                                        @if ($child['enabled'])
                                            <li>
                                                <a
                                                    href="{{ $child['url'] }}"
                                                    @if ($child['current']) aria-current="page" @endif
                                                    @class([
                                                        'flex min-h-11 items-center rounded-sm px-3 font-body text-sm focus-visible:outline-2 focus-visible:outline-offset-1 focus-visible:outline-accent',
                                                        'font-medium text-secondary' => ! $child['active'],
                                                        'text-brand font-semibold' => $child['active'],
                                                    ])
                                                >
                                                    {{ $child['label'] }}
                                                </a>
                                            </li>
                                        @endif
                                    @endforeach
                                </ul>
                            @endif
                        </li>
                    @endforeach
                </ul>

                {{-- Utility links follow the primary navigation, with Support kept as the final action. --}}
                <div class="mt-5 border-t border-line-mild pt-4">
                    <ul>
                        @foreach ($utility as $item)
                            @if ($item['label'] !== 'Support NSEE')
                                <li>
                                    <a
                                        href="{{ $item['url'] }}"
                                        @if ($item['current']) aria-current="page" @endif
                                        @class([
                                            'flex min-h-11 items-center rounded-sm font-body text-base font-medium focus-visible:outline-2 focus-visible:outline-offset-1 focus-visible:outline-accent',
                                            'text-brand font-semibold' => $item['active'],
                                        ])
                                    >
                                        {{ $item['label'] }}
                                    </a>
                                </li>
                            @endif
                        @endforeach
                    </ul>

                    @foreach ($utility as $item)
                        @if ($item['label'] === 'Support NSEE')
                            <x-ui.button
                                href="{{ $item['url'] }}"
                                size="lg"
                                :aria-current="$item['current'] ? 'page' : false"
                                class="mt-4 w-full uppercase {{ $item['active'] ? 'underline decoration-2 underline-offset-4' : '' }}"
                            >
                                {{ $item['label'] }}
                            </x-ui.button>
                        @endif
                    @endforeach
                </div>
            </nav>
        </x-ui.container>
    </div>

    {{-- Without JavaScript, expose every link and hide controls that cannot operate. --}}
    <noscript>
        <style>
            [data-mobile-menu-button], [data-mobile-submenu-button] { display: none !important; }
            [data-mobile-drawer][hidden], [data-mobile-submenu][hidden] { display: block !important; }
        </style>
    </noscript>
</header>

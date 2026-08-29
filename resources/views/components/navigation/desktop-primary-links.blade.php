@props(['items'])

<ul {{ $attributes->class('flex h-full w-full items-center justify-between gap-4 border-t border-line-white border-opacity-20') }}>
    @foreach ($items as $item)
        @php($hasDropdown = $item['label'] === 'Data & Products' && isset($item['children']))

        <li @class(['group relative', 'h-full' => $hasDropdown])>
            <a
                href="{{ $item['url'] }}"
                @if ($hasDropdown) aria-haspopup="true" @endif
                @if ($item['current']) aria-current="page" @endif
                @class([
                    'flex h-full items-center rounded-sm font-heading text-sm font-bold whitespace-nowrap text-inverse uppercase transition-opacity hover:opacity-80 focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-accent xl:text-lg',
                    'underline decoration-2 decoration-white underline-offset-8' => $item['active'],
                ])
            >
                {{ $item['label'] }}
            </a>

            {{-- Only Data & Products exposes the keyboard-accessible desktop dropdown. --}}
            @if ($hasDropdown)
                <div class="invisible absolute top-full left-1/2 z-10 min-w-48 -translate-x-1/2 pt-2 opacity-0 transition-opacity group-hover:visible group-hover:opacity-100 group-focus-within:visible group-focus-within:opacity-100">
                    <ul class="rounded-md bg-default p-2 text-primary shadow-lg ring-1 ring-line-mild">
                        @foreach ($item['children'] as $child)
                            @if ($child['enabled'])
                                <li>
                                    <a
                                        href="{{ $child['url'] }}"
                                        @if ($child['current']) aria-current="page" @endif
                                        @class([
                                            'block rounded-sm px-4 py-2 font-heading text-base font-medium whitespace-nowrap hover:bg-surface focus-visible:outline-2 focus-visible:outline-offset-1 focus-visible:outline-accent',
                                            'text-brand underline decoration-2' => $child['active'],
                                        ])
                                    >
                                        {{ $child['label'] }}
                                    </a>
                                </li>
                            @endif
                        @endforeach
                    </ul>
                </div>
            @endif
        </li>
    @endforeach
</ul>

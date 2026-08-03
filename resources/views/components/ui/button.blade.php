@props([
    'size' => 'base',
    'intent' => 'primary',
    'variant' => 'fill',
    'iconPosition' => 'none',
    'href' => null,
    'type' => 'button',
    'disabled' => false,
])

{{-- Resolve the fixed component API into validated size, intent, and state classes. --}}
@php
    $sizes = [
        'sm' => 'h-9 px-3 text-sm',
        'base' => 'h-11 px-4 text-base',
        'lg' => 'h-12 px-5 text-base',
    ];
    $iconOnlySizes = [
        'sm' => 'h-9 w-9 p-0 text-sm',
        'base' => 'h-11 w-11 p-0 text-base',
        'lg' => 'h-12 w-12 p-0 text-base',
    ];
    $styles = [
        'primary' => [
            'fill' => 'border border-transparent bg-brand text-inverse hover:bg-brand/90 active:bg-brand/80',
            'stroke' => 'border border-brand text-brand hover:bg-brand/10 active:bg-brand/20',
            'text' => 'border border-transparent text-brand hover:bg-brand/10 active:bg-brand/20',
        ],
        'secondary' => [
            'fill' => 'border border-transparent bg-primary text-inverse hover:bg-primary/90 active:bg-primary/80',
            'stroke' => 'border border-line-strong text-primary hover:bg-surface active:bg-line-mild',
            'text' => 'border border-transparent text-primary hover:bg-surface active:bg-line-mild',
        ],
        'destructive' => [
            'fill' => 'border border-transparent bg-red-600 text-inverse hover:bg-red-700 active:bg-red-800',
            'stroke' => 'border border-red-600 text-red-600 hover:border-red-700 hover:text-red-700 active:border-red-800 active:text-red-800',
            'text' => 'border border-transparent text-red-600 hover:text-red-700 active:text-red-800',
        ],
    ];
    $iconPositions = ['none', 'left', 'right', 'only'];
    $types = ['button', 'submit', 'reset'];

    // Reject unsupported values before rendering an inconsistent control.
    if (! array_key_exists($size, $sizes)) {
        throw new \InvalidArgumentException("Unsupported button size [{$size}].");
    }

    if (! array_key_exists($intent, $styles)) {
        throw new \InvalidArgumentException("Unsupported button intent [{$intent}].");
    }

    if (! array_key_exists($variant, $styles[$intent])) {
        throw new \InvalidArgumentException("Unsupported button variant [{$variant}].");
    }

    if (! in_array($iconPosition, $iconPositions, true)) {
        throw new \InvalidArgumentException("Unsupported button icon position [{$iconPosition}].");
    }

    if (! in_array($type, $types, true)) {
        throw new \InvalidArgumentException("Unsupported button type [{$type}].");
    }

    if (! is_bool($disabled)) {
        throw new \InvalidArgumentException('Button disabled must be a boolean.');
    }

    $isDisabled = $disabled;
    $isLink = $href !== null;
    $classes = [
        'inline-flex shrink-0 items-center justify-center gap-2 rounded-[2px] font-medium transition-colors',
        'focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-accent',
        'disabled:pointer-events-none disabled:cursor-not-allowed disabled:opacity-50',
        $iconPosition === 'only' ? $iconOnlySizes[$size] : $sizes[$size],
        $styles[$intent][$variant],
        $isDisabled && $isLink ? 'pointer-events-none cursor-not-allowed opacity-50' : null,
    ];
@endphp

{{-- Use an anchor for navigation and a native button for form or interface actions. --}}
@if ($isLink)
    <a
        @if ($isDisabled)
            aria-disabled="true"
            tabindex="-1"
        @else
            href="{{ $href }}"
        @endif
        {{ $attributes->except(['href', 'aria-disabled', 'tabindex'])->class($classes) }}
    >
        @if ($iconPosition === 'left' && isset($icon))
            <span class="shrink-0" aria-hidden="true">{{ $icon }}</span>
        @endif

        @if ($iconPosition !== 'only')
            {{ $slot }}
        @elseif (isset($icon))
            <span class="shrink-0" aria-hidden="true">{{ $icon }}</span>
        @endif

        @if ($iconPosition === 'right' && isset($icon))
            <span class="shrink-0" aria-hidden="true">{{ $icon }}</span>
        @endif
    </a>
@else
    <button type="{{ $type }}" @disabled($isDisabled) {{ $attributes->class($classes) }}>
        @if ($iconPosition === 'left' && isset($icon))
            <span class="shrink-0" aria-hidden="true">{{ $icon }}</span>
        @endif

        @if ($iconPosition !== 'only')
            {{ $slot }}
        @elseif (isset($icon))
            <span class="shrink-0" aria-hidden="true">{{ $icon }}</span>
        @endif

        @if ($iconPosition === 'right' && isset($icon))
            <span class="shrink-0" aria-hidden="true">{{ $icon }}</span>
        @endif
    </button>
@endif

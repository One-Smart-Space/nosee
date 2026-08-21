@props([
    'description',
    'linkGroups',
    'contact',
    'socialLinks',
    'legalLinks',
    'newsletter',
    'supportUrl',
    'copyright',
])

{{-- Prepare shared link behaviour and external-link safety once for every footer group. --}}
@php
    $isExternal = static fn (string $url): bool => str_starts_with($url, 'https://') || str_starts_with($url, 'http://');
    $footerLinkClasses = implode(' ', [
        'relative inline-flex min-h-11 items-center text-tertiary transition-colors hover:text-inverse',
        'focus-visible:text-inverse focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-accent',
        'lg:min-h-0 lg:after:absolute lg:after:-inset-y-1 lg:after:inset-x-0',
        'lg:after:bg-[linear-gradient(currentColor,currentColor)] lg:after:bg-[length:0_1px] lg:after:bg-left-bottom lg:after:bg-no-repeat',
        'lg:after:transition-[background-size] lg:after:duration-300 lg:hover:after:bg-[length:100%_1px] lg:focus-visible:after:bg-[length:100%_1px]',
    ]);
    $supportIsExternal = $isExternal($supportUrl);
    $phoneHref = preg_replace('/[^+\d]/', '', $contact['phone']);
    $newsletterEnabled = $newsletter['enabled'];
    $newsletterActionIsInternal = str_starts_with((string) $newsletter['action'], '/');
@endphp

<footer {{ $attributes->class('overflow-x-clip bg-primary font-body text-secondary') }} data-site-footer>
    <x-ui.container class="py-16 lg:pt-24 lg:pb-8">
        {{-- Reorder the same semantic regions to match the desktop columns and mobile content flow. --}}
        <div class="grid gap-12 lg:grid-cols-12 lg:gap-8" data-footer-main>
            <section class="order-1 lg:col-span-3" aria-label="About NOSEE" data-footer-organisation>
                <img
                    src="/logoWhite.png"
                    alt="NOSEE - Network of Space-Earth Environmentalists"
                    width="160"
                    height="48"
                    class="h-auto w-40 max-w-full"
                >

                @if ($description !== '')
                    <p class="mt-5 max-w-xs text-sm leading-6 text-tertiary">{{ $description }}</p>
                @endif

                <ul class="mt-8 flex flex-wrap items-center gap-3" aria-label="Social media">
                    @foreach ($socialLinks as $socialLink)
                        @php($socialIsExternal = $isExternal($socialLink['url']))

                        <li>
                            <a
                                href="{{ $socialLink['url'] }}"
                                aria-label="{{ $socialLink['label'] }}"
                                @if ($socialIsExternal)
                                    target="_blank"
                                    rel="noopener noreferrer"
                                @endif
                                class="group flex min-h-11 min-w-11 items-center justify-center rounded-sm p-2 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-accent lg:min-h-0 lg:min-w-0"
                            >
                                <span
                                    aria-hidden="true"
                                    class="size-6 bg-tertiary transition-colors group-hover:bg-inverse group-focus-visible:bg-inverse"
                                    style="-webkit-mask: url('{{ $socialLink['icon'] }}') center / contain no-repeat; mask: url('{{ $socialLink['icon'] }}') center / contain no-repeat;"
                                ></span>
                            </a>
                        </li>
                    @endforeach
                </ul>

                <x-ui.button
                    href="{{ $supportUrl }}"
                    intent="secondary"
                    variant="stroke"
                    size="lg"
                    :target="$supportIsExternal ? '_blank' : null"
                    :rel="$supportIsExternal ? 'noopener noreferrer' : null"
                    class="mt-8 bg-default px-6 uppercase tracking-[0.02em]"
                >
                    Support NOSEE
                </x-ui.button>
            </section>

            {{-- Keep the newsletter second on mobile while placing it at the far right on desktop. --}}
            <section class="order-2 lg:order-4 lg:col-span-4" aria-labelledby="footer-newsletter-heading" data-footer-newsletter>
                <h2 id="footer-newsletter-heading" class="font-heading text-2xl font-semibold text-tertiary lg:text-xl">
                    {{ $newsletter['heading'] }}
                </h2>
                <p class="mt-4 leading-7">{{ $newsletter['description'] }}</p>

                <form
                    @if ($newsletterEnabled)
                        action="{{ $newsletter['action'] }}"
                        method="post"
                    @else
                        aria-disabled="true"
                        aria-describedby="footer-newsletter-disabled"
                    @endif
                    class="mt-4 flex min-w-0 border-2 border-default bg-default"
                    aria-label="Newsletter subscription"
                    data-newsletter-enabled="{{ $newsletterEnabled ? 'true' : 'false' }}"
                    @if ($newsletterEnabled) data-newsletter-form @endif
                >
                    @if ($newsletterEnabled && $newsletterActionIsInternal)
                        @csrf
                    @endif

                    <label for="footer-newsletter-email" class="sr-only">{{ $newsletter['label'] }}</label>
                    <input
                        id="footer-newsletter-email"
                        name="email"
                        type="email"
                        autocomplete="email"
                        maxlength="254"
                        required
                        placeholder="{{ $newsletter['placeholder'] }}"
                        @disabled(! $newsletterEnabled)
                        @if ($newsletterEnabled) data-newsletter-email @endif
                        class="h-12 min-w-0 flex-1 bg-default px-4 text-primary outline-none placeholder:text-secondary disabled:bg-surface disabled:text-disabled disabled:placeholder:text-disabled"
                    >
                    <x-ui.button
                        type="submit"
                        size="lg"
                        :disabled="! $newsletterEnabled"
                        :data-newsletter-submit="$newsletterEnabled ? true : null"
                        class="rounded-none px-5"
                    >
                        {{ $newsletter['button_label'] }}
                    </x-ui.button>
                </form>

                @if (! $newsletterEnabled)
                    <p id="footer-newsletter-disabled" class="mt-2 text-sm text-tertiary">
                        {{ $newsletter['disabled_text'] }}
                    </p>
                @endif

                <p class="mt-2 text-sm text-red-300" role="alert" data-newsletter-error hidden>
                    Unable to subscribe right now. Please try again.
                </p>
            </section>

            {{-- Contact details remain readable text with direct email and telephone actions. --}}
            <section class="order-3 lg:order-2 lg:col-span-3" aria-labelledby="footer-contact-heading" data-footer-contact>
                <h2 id="footer-contact-heading" class="font-heading text-2xl font-semibold text-tertiary lg:text-xl">
                    {{ $contact['heading'] }}
                </h2>
                <address class="mt-5 not-italic leading-7">
                    <p class="max-w-sm">{{ $contact['address'] }}</p>
                    <a class="mt-3 {{ $footerLinkClasses }}" href="mailto:{{ $contact['email'] }}">
                        {{ $contact['email'] }}
                    </a>
                    <br>
                    @if ($phoneHref !== '')
                        <a class="mt-1 {{ $footerLinkClasses }}" href="tel:{{ $phoneHref }}">
                            {{ $contact['phone'] }}
                        </a>
                    @else
                        <p class="mt-3">{{ $contact['phone'] }}</p>
                    @endif
                </address>
            </section>

            {{-- Link groups use one reusable desktop-only pseudo-element for both hit area and underline. --}}
            <div class="order-4 space-y-10 lg:order-3 lg:col-span-2" data-footer-link-groups>
                @foreach ($linkGroups as $linkGroup)
                    <nav aria-labelledby="footer-link-group-{{ $loop->index }}">
                        <h2 id="footer-link-group-{{ $loop->index }}" class="font-heading text-2xl font-semibold text-tertiary lg:text-xl">
                            {{ $linkGroup['heading'] }}
                        </h2>
                        <ul class="mt-4 space-y-1 lg:space-y-3">
                            @foreach ($linkGroup['links'] as $link)
                                @php($linkIsExternal = $isExternal($link['url']))

                                <li>
                                    <a
                                        href="{{ $link['url'] }}"
                                        @if ($linkIsExternal)
                                            target="_blank"
                                            rel="noopener noreferrer"
                                        @endif
                                        class="{{ $footerLinkClasses }}"
                                        data-footer-link
                                    >
                                        {{ $link['label'] }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </nav>
                @endforeach
            </div>
        </div>

        {{-- Separate legal navigation from the main content without changing its responsive reading order. --}}
        <div class="mt-14 border-t border-white/20 pt-8 lg:mt-24 lg:flex lg:items-center lg:justify-between" data-footer-legal-row>
            <p>{{ $copyright }}</p>
            <nav class="mt-3" aria-label="Legal">
                <ul class="flex flex-wrap items-center gap-x-5 gap-y-1">
                    @foreach ($legalLinks as $legalLink)
                        @php($legalIsExternal = $isExternal($legalLink['url']))

                        <li class="flex items-center gap-5">
                            @if (! $loop->first)
                                <span aria-hidden="true">/</span>
                            @endif
                            <a
                                href="{{ $legalLink['url'] }}"
                                @if ($legalIsExternal)
                                    target="_blank"
                                    rel="noopener noreferrer"
                                @endif
                                class="{{ $footerLinkClasses }}"
                                data-footer-link
                            >
                                {{ $legalLink['label'] }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </nav>
        </div>
    </x-ui.container>

    {{-- Confirm successful subscriptions in a native modal with built-in keyboard semantics. --}}
    @if ($newsletterEnabled)
        <dialog
            class="m-auto w-[calc(100%-2rem)] max-w-md rounded-md bg-default p-0 text-primary shadow-2xl backdrop:bg-black/70"
            aria-labelledby="newsletter-confirmation-title"
            aria-describedby="newsletter-confirmation-message"
            data-newsletter-dialog
        >
            <div class="p-6 text-center sm:p-8">
                <h2 id="newsletter-confirmation-title" class="font-heading text-2xl font-semibold">
                    Subscription confirmed
                </h2>
                <p id="newsletter-confirmation-message" class="mt-4 leading-7 text-secondary">
                    Thank you for subscribing! Look out for NOSEE's newletter in your mailbox
                </p>
                <x-ui.button type="button" class="mt-6" data-newsletter-dismiss>
                    Dismiss
                </x-ui.button>
            </div>
        </dialog>
    @endif
</footer>

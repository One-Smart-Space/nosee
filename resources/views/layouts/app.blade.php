<!DOCTYPE html>
@php($navigationOverlaysContent = $navigationOverlaysContent ?? false)
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        {{-- Shared document metadata, bundled assets, and page-specific head additions. --}}
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>@yield('title', config('app.name', 'NOSEE'))</title>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @stack('head')
    </head>
    <body class="[--compact-navbar-offset:4.5rem] [--expanded-navbar-offset:clamp(12.3125rem,calc(5.8555rem+32.2266vw),16.5rem)] lg:[--compact-navbar-offset:6.5rem] lg:[--expanded-navbar-offset:8.75rem] xl:[--expanded-navbar-offset:10.25rem]">
        {{-- Let keyboard users bypass repeated navigation and reach page content directly. --}}
        <a
            href="#main-content"
            class="sr-only focus:not-sr-only focus:fixed focus:top-4 focus:left-4 focus:z-50 focus:rounded-md focus:bg-default focus:px-4 focus:py-2 focus:text-primary focus:outline-2 focus:outline-offset-2 focus:outline-accent"
        >
            Skip to main content
        </a>

        {{-- Start every navigation expanded and let the shared scroll state compact both variants. --}}
        <x-navigation.desktop-navigation :transparent="true" />
        <x-navigation.mobile-navigation :transparent="true" />

        {{-- Offset regular pages below the expanded header while the homepage remains overlaid. --}}
        <main id="main-content" @class(['pt-[var(--expanded-navbar-offset)]' => ! $navigationOverlaysContent])>
            @yield('content')
        </main>

        {{-- Render validated shared footer content after every page using this layout. --}}
        <x-footer.site-footer
            :description="$footer['description']"
            :link-groups="$footer['link_groups']"
            :contact="$footer['contact']"
            :social-links="$footer['social_links']"
            :legal-links="$footer['legal_links']"
            :newsletter="$footer['newsletter']"
            :support-url="$footer['support_url']"
            :copyright="$footer['copyright']"
        />
    </body>
</html>

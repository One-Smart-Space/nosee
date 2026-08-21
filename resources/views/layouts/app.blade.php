<!DOCTYPE html>
@php($transparentNavigation = $transparentNavigation ?? false)
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        {{-- Shared document metadata, bundled assets, and page-specific head additions. --}}
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>@yield('title', config('app.name', 'NOSEE'))</title>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @stack('head')
    </head>
    <body class="[--compact-navbar-offset:4.5rem] lg:[--compact-navbar-offset:6.5rem]">
        {{-- Let keyboard users bypass repeated navigation and reach page content directly. --}}
        <a
            href="#main-content"
            class="sr-only focus:not-sr-only focus:fixed focus:top-4 focus:left-4 focus:z-50 focus:rounded-md focus:bg-default focus:px-4 focus:py-2 focus:text-primary focus:outline-2 focus:outline-offset-2 focus:outline-accent"
        >
            Skip to main content
        </a>

        {{-- Render one responsive navigation system with the same transparency setting. --}}
        <x-navigation.desktop-navigation :transparent="$transparentNavigation" />
        <x-navigation.mobile-navigation :transparent="$transparentNavigation" />

        {{-- Offset regular pages below the fixed header while transparent pages may sit underneath it. --}}
        <main id="main-content" @class(['pt-[4.5rem] lg:pt-16' => ! $transparentNavigation])>
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

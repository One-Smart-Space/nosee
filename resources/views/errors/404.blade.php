@extends('layouts.app')

@section('title', 'Page not found — NOSEE')

@section('content')
    {{-- Keep missing pages inside the standard application shell and navigation. --}}
    <x-ui.container class="pt-10 pb-16 lg:pt-6">
        <h1 class="font-heading text-2xl font-semibold">Page not found</h1>
    </x-ui.container>
@endsection

@extends('layouts.app')

@section('title', 'NOSEE')

@section('content')
    {{-- Lead the homepage with editorial hero items supplied by the content service. --}}
    <x-home.hero :items="$heroItems" />

    {{-- Surface repository-selected monitoring dashboards directly after the hero. --}}
    <x-home.monitoring-dashboard :records="$monitoringRecords" />

    {{-- Follow monitoring data with the three newest featured research outputs. --}}
    <x-home.research-outputs :records="$researchOutputs" />

    {{-- Follow research outputs with the featured and newest news records. --}}
    <x-home.trending-news :articles="$trendingArticles" />

    {{-- Finish this homepage milestone with the three featured upcoming events. --}}
    <x-home.upcoming-events :events="$upcomingEvents" />
@endsection

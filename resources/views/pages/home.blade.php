@extends('layouts.app')

@section('title', 'NOSEE')

@section('content')
    {{-- Lead the homepage with editorial hero items supplied by the content service. --}}
    <x-home.hero :items="$heroItems" />

    {{-- Surface repository-selected monitoring dashboards directly after the hero. --}}
    <x-home.monitoring-dashboard :records="$monitoringRecords" />
@endsection

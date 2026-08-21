<?php

// Development fixture only; not approved NOSEE event information.
return [
    'slug' => 'regional-space-weather-conference',
    'title' => 'Regional Space Weather Research Conference',
    'type' => 'conference',
    'summary' => 'A development programme for sharing observational methods, forecasting studies, and regional research priorities.',
    'featured' => true,
    'image' => null,
    'image_alt' => null,
    'timezone' => 'Africa/Lagos',
    'start_date' => '2027-03-10',
    'end_date' => '2027-03-12',
    'schedule' => [
        'mode' => 'multi_day',
        'start_time' => null,
        'end_time' => null,
        'itinerary' => [
            [
                'date' => '2027-03-10',
                'start_time' => '09:00',
                'end_time' => '17:00',
                'title' => 'Regional observations',
                'description' => 'Development presentations on observation networks and shared methods.',
            ],
            [
                'date' => '2027-03-11',
                'start_time' => '09:00',
                'end_time' => '17:00',
                'title' => 'Forecasting studies',
                'description' => 'Development presentations and working sessions on forecasting research.',
            ],
            [
                'date' => '2027-03-12',
                'start_time' => '09:00',
                'end_time' => '14:00',
                'title' => 'Research priorities',
                'description' => 'Development discussion of possible regional research priorities.',
            ],
        ],
    ],
    'location' => [
        'type' => 'physical',
        'venue' => 'Development Conference Centre',
        'address' => 'Research District',
        'city' => 'Abuja',
        'country' => 'Nigeria',
        'platform' => null,
    ],
    'application_deadline' => null,
    'meeting_site_url' => null,
    'organiser' => 'NOSEE Development Conference Team',
    'speakers' => ['Development Conference Speaker'],
    'registration_url' => 'https://example.org/events/regional-space-weather-conference',
    'body' => 'This fictional three-day conference record provides development detail content for presentations, working sessions, and research discussions.',
    'resources' => [],
];

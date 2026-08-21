<?php

// Development fixture only; not approved NOSEE event information.
return [
    'slug' => 'earth-observation-data-workshop',
    'title' => 'Earth Observation Data Methods Workshop',
    'type' => 'workshop',
    'summary' => 'A practical development workshop exploring reproducible processing and interpretation of environmental satellite data.',
    'featured' => true,
    'image' => null,
    'image_alt' => null,
    'timezone' => 'Africa/Lagos',
    'start_date' => '2027-04-21',
    'end_date' => '2027-04-22',
    'schedule' => [
        'mode' => 'multi_day',
        'start_time' => null,
        'end_time' => null,
        'itinerary' => [
            [
                'date' => '2027-04-21',
                'start_time' => '09:00',
                'end_time' => '16:00',
                'title' => 'Data preparation',
                'description' => 'Development exercises for sourcing, checking, and preparing satellite observations.',
            ],
            [
                'date' => '2027-04-22',
                'start_time' => '09:30',
                'end_time' => '15:30',
                'title' => 'Analysis workflows',
                'description' => 'Development exercises for reproducible analysis and interpretation.',
            ],
        ],
    ],
    'location' => [
        'type' => 'hybrid',
        'venue' => 'Development Geospatial Laboratory',
        'address' => 'University Road',
        'city' => 'Lagos',
        'country' => 'Nigeria',
        'platform' => 'Zoom',
    ],
    'application_deadline' => '2027-04-07T17:00:00+01:00',
    'meeting_site_url' => null,
    'organiser' => 'NOSEE Development Data Team',
    'speakers' => ['Development Workshop Facilitator'],
    'registration_url' => 'https://example.org/events/earth-observation-data-workshop',
    'body' => 'This fictional workshop record provides development detail content for a two-day sequence of practical earth-observation exercises.',
    'resources' => [
        [
            'label' => 'Development workshop requirements',
            'url' => 'https://example.org/resources/earth-observation-requirements',
        ],
    ],
];

<?php

return [
    'primary' => [
        [
            'label' => 'About NSEE',
            'url' => '/about',
            'enabled' => true,
            'children' => [
                ['label' => 'Mission', 'url' => '/about#mission', 'enabled' => true],
                ['label' => 'Leadership', 'url' => '/about#leadership', 'enabled' => true],
                ['label' => 'Collaborations', 'url' => '/about#collaborations', 'enabled' => true],
            ],
        ],
        [
            'label' => 'Research',
            'url' => '/research',
            'enabled' => true,
            'children' => [
                ['label' => 'Atmosphere and Air Quality', 'url' => '/research/atmosphere-and-air-quality', 'enabled' => true],
                ['label' => 'Climate Science', 'url' => '/research/climate-science', 'enabled' => true],
                ['label' => 'Earth and Space Informatics', 'url' => '/research/earth-and-space-informatics', 'enabled' => true],
                ['label' => 'Energy, Resources and Environment', 'url' => '/research/energy-resources-and-environment', 'enabled' => true],
                ['label' => 'Space Weather', 'url' => '/research/space-weather', 'enabled' => true],
            ],
        ],
        [
            'label' => 'Data & Products',
            'url' => '/data',
            'enabled' => true,
            'children' => [
                ['label' => 'Data', 'url' => '/data', 'enabled' => true],
                ['label' => 'Products', 'url' => '/products', 'enabled' => true],
            ],
        ],
        ['label' => 'Meetings', 'url' => '/meetings', 'enabled' => true],
        ['label' => 'Publications', 'url' => '/publications', 'enabled' => true],
        ['label' => 'Outreach', 'url' => '/outreach', 'enabled' => true],
    ],
    'utility' => [
        ['label' => 'News', 'url' => '/news', 'enabled' => true],
        ['label' => 'Events', 'url' => '/events', 'enabled' => true],
        ['label' => 'Multimedia', 'url' => '/multimedia', 'enabled' => true],
        ['label' => 'Support NSEE', 'url' => '/support', 'enabled' => true],
        [
            'label' => 'Login',
            'url' => null,
            'enabled' => false,
            'version' => 'v1',
        ],
    ],
];

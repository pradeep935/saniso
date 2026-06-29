<?php

return [
    'homepage_cache' => [
        'enabled' => env('SANISO_HOMEPAGE_CACHE_ENABLED', true),
        'ttl' => (int) env('SANISO_HOMEPAGE_CACHE_TTL', 300),
    ],
];

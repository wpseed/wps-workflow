<?php

return [
    'api' => [
        'client_id' => env('BITBUCKET_API_CLIENT_ID', false),
        'client_secret' => env('BITBUCKET_API_CLIENT_SECRET', false),
    ],
    'accounts' => [
        'plugins' => env('BITBUCKET_PLUGINS_ACCOUNT', 'wpseed-plugin'),
        'themes' => env('BITBUCKET_THEMES_ACCOUNT', 'wpseed-theme'),
    ]
];

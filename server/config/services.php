<?php

return [
    'gemini' => [
        'api_key' => env('GEMINI_API_KEY'),
        'model' => env('GEMINI_MODEL', 'gemini-1.5-flash'),
        'base_uri' => env('GEMINI_BASE_URI', 'https://generativelanguage.googleapis.com/v1beta/'),
        'ca_bundle' => env('GEMINI_CA_BUNDLE'),
    ],
];

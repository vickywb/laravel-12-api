<?php

return [

    'paths' => ['api/*', 'login', 'logout'],

    'allowed_methods' => ['*'],

    'allowed_origins' => ['http://localhost:5173'], // sesuai port FE Vue

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    // supaya browser bisa akses set-cookie / kirim cookie
    'exposed_headers' => ['Authorization'],

    'max_age' => 0,

    'supports_credentials' => true,
];

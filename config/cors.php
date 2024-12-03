<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your settings for cross-origin resource sharing
    | or "CORS". This determines what cross-origin operations may execute
    | in web browsers. You are free to adjust these settings as needed.
    |
    | To learn more: https://developer.mozilla.org/en-US/docs/Web/HTTP/CORS
    |
    */

    // 'paths' => ['api/*', 'sanctum/csrf-cookie'],
    // 'allowed_methods' => ['*'],
    // 'allowed_origins' => ['*'],
    // 'allowed_origins_patterns' => [],
    // 'allowed_headers' => ['*'],
    // 'exposed_headers' => [],
    // 'max_age' => 0,
    // 'supports_credentials' => true,


    // 'paths' => ['api/*'],
    // 'allowed_methods' => ['*'],
    // 'allowed_origins' => ['http://103.155.161.244:5173'],
    // 'allowed_origins_patterns' => [],
    // 'allowed_headers' => ['Content-Type', 'X-Requested-With', 'Authorization'],
    // 'exposed_headers' => ['Set-Cookie'],
    // 'max_age' => 0,
    // 'supports_credentials' => true,

    'paths' => ['api/*'],
    'allowed_methods' => ['*'],
    'allowed_origins' => ['http://pham-huynh.io.vn'],
    'allowed_origins_patterns' => [],
    'allowed_headers' => ['Content-Type', 'X-Requested-With', 'Authorization'],
    'exposed_headers' => ['Set-Cookie'],
    'max_age' => 0,
    'supports_credentials' => true,


];

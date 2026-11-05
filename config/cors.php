<?php
return [
    'paths' => ['api/*', 'login', 'register', 'sanctum/csrf-cookie', 'broadcasting/auth'],
    'allowed_methods' => ['*'],
    'allowed_origins' => ['http://localhost:5173'], // ← порт вашего Vue
    'allowed_origins_patterns' => [],
    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => true, // ← ВАЖНО для cookies
];


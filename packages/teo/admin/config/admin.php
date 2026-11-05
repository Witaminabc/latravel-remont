<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Admin route prefix
    |--------------------------------------------------------------------------
    | SPA fallback + API prefix for the admin panel.
    */
    'route_prefix' => env('ADMIN_ROUTE_PREFIX', 'admin'),

    /*
    |--------------------------------------------------------------------------
    | API prefix
    |--------------------------------------------------------------------------
    | Base prefix for admin JSON API routes (auth:sanctum protected).
    */
    'api_prefix' => env('ADMIN_API_PREFIX', 'api/admin'),

    /*
    |--------------------------------------------------------------------------
    | Admin role
    |--------------------------------------------------------------------------
    | Users whose "role" column equals this value pass the admin middleware.
    */
    'admin_role' => env('ADMIN_ROLE', 'admin'),

    /*
    |--------------------------------------------------------------------------
    | Auth guard
    |--------------------------------------------------------------------------
    */
    'guard' => 'sanctum',

];

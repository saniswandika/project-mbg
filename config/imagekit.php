<?php

return [

    /*
    |--------------------------------------------------------------------------
    | API Setup
    |--------------------------------------------------------------------------
    |
    | Enter your keys and endpoint
    |
    */

    'public' => env('IMAGEKIT_PUBLIC_KEY', ''),
    'private' => env('IMAGEKIT_PRIVATE_KEY', ''),
    'endpoint' => env('IMAGEKIT_URL_ENDPOINT', ''),

    /*
    |--------------------------------------------------------------------------
    | Cache options
    |--------------------------------------------------------------------------
    |
    | purge_cache_update - if set to true a cache clear request is going to be made
    | on file update and delete for the given path.
    | Read more about cache here: https://docs.imagekit.io/features/caches
    |
    */

    'purge_cache_update' => true,

    /*
    |--------------------------------------------------------------------------
    | Folder options
    |--------------------------------------------------------------------------
    |
    | include_folders - if set to true folders will also be returned when using listContents()
    |
    */

    'include_folders' => true,
];

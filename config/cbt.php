<?php

return [
    /*
    |--------------------------------------------------------------------------
    | CBT Default Driver
    |--------------------------------------------------------------------------
    |
    | Driver aktif untuk engine Computer Based Test (CBT).
    | Default: 'zencbt'
    |
    */
    'driver' => env('CBT_DRIVER', 'zencbt'),

    /*
    |--------------------------------------------------------------------------
    | ZenCBT Bridge API Gateway Configuration
    |--------------------------------------------------------------------------
    |
    | URL dan Kredensial Bearer Token untuk mengakses microservice zencbt-bridge.
    | Catatan: ZenCbtService akan memprioritaskan konfigurasi di tabel
    | school_settings (PengaturanSekolah::current()) jika sudah diisi.
    |
    */
    'api_url' => env('CBT_API_URL', 'http://127.0.0.1:7879'),
    'api_key' => env('CBT_API_KEY'),

    /*
    |--------------------------------------------------------------------------
    | HTTP Client Connection Timeouts (Seconds)
    |--------------------------------------------------------------------------
    */
    'timeout' => (int) env('CBT_TIMEOUT', 15),
    'connect_timeout' => (int) env('CBT_CONNECT_TIMEOUT', 5),

    /*
    |--------------------------------------------------------------------------
    | Batch Size for Bulk Student Synchronization
    |--------------------------------------------------------------------------
    */
    'chunk_size' => 50,

    /*
    |--------------------------------------------------------------------------
    | Retry Configuration for Transient Failures
    |--------------------------------------------------------------------------
    */
    'retry' => [
        'times' => 3,
        'sleep' => 1000, // milliseconds
    ],
];

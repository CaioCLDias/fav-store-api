<?php

return [
    /*
    |--------------------------------------------------------------------------
    | FakeStore API Configuration
    |--------------------------------------------------------------------------
    |
    |
    */
    'fakestore' => [
        'base_url' => env('FAKESTORE_API_URL', 'https://fakestoreapi.com'),
        'timeout' => env('FAKESTORE_API_TIMEOUT', 10),
        'max_retries' => env('FAKESTORE_API_MAX_RETRIES', 3),
        'cache_ttl' => env('FAKESTORE_API_CACHE_TTL', 3600),
        'rate_limit' => [
            'max' => env('FAKESTORE_API_RATE_LIMIT_MAX', 100),
            'window' => env('FAKESTORE_API_RATE_LIMIT_WINDOW', 60),
            'key' => 'fakestore_api_requests',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Product Cache Configuration
    |--------------------------------------------------------------------------
    |
    |
    */
    'cache' => [
        'products' => [
            'ttl' => env('PRODUCT_CACHE_TTL', 1800), 
            'search_ttl' => env('PRODUCT_SEARCH_CACHE_TTL', 900), 
            'health_check_ttl' => env('PRODUCT_HEALTH_CHECK_TTL', 300), 
        ],
        'prefix' => env('CACHE_PREFIX', 'app') . '_products',
    ],

    /*
    |--------------------------------------------------------------------------
    | Product Sync Configuration
    |--------------------------------------------------------------------------
    |
    |
    */
    'sync' => [
        'enabled' => env('PRODUCT_SYNC_ENABLED', true),
        'interval' => env('PRODUCT_SYNC_INTERVAL', 3600), 
        'cleanup_enabled' => env('PRODUCT_CLEANUP_ENABLED', true),
        'warmup_popular_products' => env('PRODUCT_WARMUP_POPULAR', true),
        'popular_products_limit' => env('PRODUCT_WARMUP_LIMIT', 20),
    ],

    /*
    |--------------------------------------------------------------------------
    | Product Search Configuration
    |--------------------------------------------------------------------------
    |
    |
    */
    'search' => [
        'max_results' => env('PRODUCT_SEARCH_MAX_RESULTS', 100),
        'min_query_length' => env('PRODUCT_SEARCH_MIN_QUERY_LENGTH', 2),
        'relevance_weights' => [
            'title' => 10,
            'title_start' => 5,
            'category' => 5,
            'description' => 2,
            'rating_bonus' => 1,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Fallback Configuration
    |--------------------------------------------------------------------------
    |
    |
    */
    'fallback' => [
        'enabled' => env('API_FALLBACK_ENABLED', true),
        'cache_extend' => env('API_FALLBACK_CACHE_EXTEND', 7200), 
        'retry_after' => env('API_FALLBACK_RETRY_AFTER', 300), 
    ],

    /*
    |--------------------------------------------------------------------------
    | Monitoring Configuration
    |--------------------------------------------------------------------------
    |
    |
    */
    'monitoring' => [
        'health_check_enabled' => env('API_HEALTH_CHECK_ENABLED', true),
        'health_check_interval' => env('API_HEALTH_CHECK_INTERVAL', 300),
        'alert_threshold' => env('API_ALERT_THRESHOLD', 5000), 
        'log_slow_requests' => env('API_LOG_SLOW_REQUESTS', true),
        'slow_request_threshold' => env('API_SLOW_REQUEST_THRESHOLD', 2000), 
    ],
];
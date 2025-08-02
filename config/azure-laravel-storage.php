<?php

// config for Owlfice/AzureLaravelStorage
return [

    /*
    |--------------------------------------------------------------------------
    | Default Azure Storage Connection
    |--------------------------------------------------------------------------
    |
    | Here you can specify which of the azure storage connections below you
    | wish to use as your default connection for all azure storage work.
    |
    */

    'default' => env('AZURE_STORAGE_DEFAULT', 'azure'),

    /*
    |--------------------------------------------------------------------------
    | Azure Storage Connections
    |--------------------------------------------------------------------------
    |
    | Here you can define multiple azure storage connections. Each connection
    | represents a different Azure Storage account that you may want to
    | interact with from your application.
    |
    */

    'connections' => [
        'azure' => [
            'driver' => 'azure',
            'account_name' => env('AZURE_STORAGE_ACCOUNT_NAME'),
            'account_key' => env('AZURE_STORAGE_ACCOUNT_KEY'),
            'container' => env('AZURE_STORAGE_CONTAINER', 'default'),
            'endpoint' => env('AZURE_STORAGE_ENDPOINT'),
            'url' => env('AZURE_STORAGE_URL'),
            'visibility' => env('AZURE_STORAGE_VISIBILITY', 'public'),
            'timeout' => env('AZURE_STORAGE_TIMEOUT', 300),
            'verify_ssl' => env('AZURE_STORAGE_VERIFY_SSL', true),
        ],

        'azure_sas' => [
            'driver' => 'azure',
            'account_name' => env('AZURE_STORAGE_SAS_ACCOUNT_NAME'),
            'sas_token' => env('AZURE_STORAGE_SAS_TOKEN'),
            'container' => env('AZURE_STORAGE_SAS_CONTAINER', 'default'),
            'endpoint' => env('AZURE_STORAGE_SAS_ENDPOINT'),
            'url' => env('AZURE_STORAGE_SAS_URL'),
            'visibility' => env('AZURE_STORAGE_SAS_VISIBILITY', 'public'),
            'timeout' => env('AZURE_STORAGE_SAS_TIMEOUT', 300),
            'verify_ssl' => env('AZURE_STORAGE_SAS_VERIFY_SSL', true),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Azure Blob Storage Settings
    |--------------------------------------------------------------------------
    |
    | Configure default settings for Azure Blob Storage operations
    |
    */

    'blob' => [
        'chunk_size' => env('AZURE_BLOB_CHUNK_SIZE', 4 * 1024 * 1024), // 4MB chunks
        'max_retry_attempts' => env('AZURE_BLOB_MAX_RETRY_ATTEMPTS', 3),
        'retry_delay' => env('AZURE_BLOB_RETRY_DELAY', 1000), // milliseconds
        'timeout' => env('AZURE_BLOB_TIMEOUT', 300), // seconds
        'concurrent_uploads' => env('AZURE_BLOB_CONCURRENT_UPLOADS', 5),
    ],

    /*
    |--------------------------------------------------------------------------
    | Content Type Detection
    |--------------------------------------------------------------------------
    |
    | Enable automatic content type detection based on file extensions
    |
    */

    'auto_detect_content_type' => env('AZURE_AUTO_DETECT_CONTENT_TYPE', true),

    /*
    |--------------------------------------------------------------------------
    | Metadata Settings
    |--------------------------------------------------------------------------
    |
    | Configure default metadata to be added to uploaded files
    |
    */

    'default_metadata' => [
        'uploaded_by' => env('APP_NAME', 'Laravel'),
        'environment' => env('APP_ENV', 'production'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Security Settings
    |--------------------------------------------------------------------------
    |
    | Configure security-related settings for Azure Storage operations
    |
    */

    'security' => [
        'encrypt_at_rest' => env('AZURE_ENCRYPT_AT_REST', true),
        'require_https' => env('AZURE_REQUIRE_HTTPS', true),
        'allowed_file_types' => env('AZURE_ALLOWED_FILE_TYPES', 'jpg,jpeg,png,gif,pdf,doc,docx,txt,zip'),
        'max_file_size' => env('AZURE_MAX_FILE_SIZE', 100 * 1024 * 1024), // 100MB
    ],

    /*
    |--------------------------------------------------------------------------
    | CDN Settings
    |--------------------------------------------------------------------------
    |
    | Configure CDN settings if you're using Azure CDN
    |
    */

    'cdn' => [
        'enabled' => env('AZURE_CDN_ENABLED', false),
        'endpoint' => env('AZURE_CDN_ENDPOINT'),
        'profile' => env('AZURE_CDN_PROFILE'),
        'resource_group' => env('AZURE_CDN_RESOURCE_GROUP'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Logging Settings
    |--------------------------------------------------------------------------
    |
    | Configure logging for Azure Storage operations
    |
    */

    'logging' => [
        'enabled' => env('AZURE_STORAGE_LOGGING_ENABLED', true),
        'channel' => env('AZURE_STORAGE_LOG_CHANNEL', 'single'),
        'level' => env('AZURE_STORAGE_LOG_LEVEL', 'info'),
        'log_requests' => env('AZURE_STORAGE_LOG_REQUESTS', false),
        'log_responses' => env('AZURE_STORAGE_LOG_RESPONSES', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache Settings
    |--------------------------------------------------------------------------
    |
    | Configure caching for metadata and container information
    |
    */

    'cache' => [
        'enabled' => env('AZURE_STORAGE_CACHE_ENABLED', true),
        'ttl' => env('AZURE_STORAGE_CACHE_TTL', 3600), // 1 hour
        'prefix' => env('AZURE_STORAGE_CACHE_PREFIX', 'azure_storage'),
        'store' => env('AZURE_STORAGE_CACHE_STORE'), // uses default cache store if null
    ],

    /*
    |--------------------------------------------------------------------------
    | Queue Settings
    |--------------------------------------------------------------------------
    |
    | Configure background processing for large file operations
    |
    */

    'queue' => [
        'enabled' => env('AZURE_STORAGE_QUEUE_ENABLED', false),
        'connection' => env('AZURE_STORAGE_QUEUE_CONNECTION', 'default'),
        'queue' => env('AZURE_STORAGE_QUEUE_NAME', 'azure-storage'),
        'large_file_threshold' => env('AZURE_STORAGE_LARGE_FILE_THRESHOLD', 50 * 1024 * 1024), // 50MB
    ],

];

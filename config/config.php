<?php

return [
    'sync' => [
        'default_chunk_size' => env('SYNCHRONIZER_CHUNK_SIZE', 250),
    ],

    'checksum' => [
        'control' => env('SYNCHRONIZER_CHECKSUM_CONTROL', true),
        'field' => env('SYNCHRONIZER_CHECKSUM_FIELD', 'checksum'),
        'timestamps' => [
            'created_at',
            'updated_at',
        ],
        'timestamps_excluded' => false,
    ],

    'logger' => [
        'db' => env('SYNCHRONIZER_DB_LOGGING', true),

        'notifications' => [
            'slack' => env('SYNCHRONIZER_SLACK_WEBHOOK', null),
        ],
    ],

    'jobs' => [
        'default' => [
            \Grixu\Synchronizer\Process\Jobs\LoadDataToSyncJob::class,
            \Grixu\Synchronizer\Process\Jobs\ParseLoadedDataJob::class,
            \Grixu\Synchronizer\Process\Jobs\SyncParsedDataJob::class,
        ],
        'load-all-and-parse' => [
            \Grixu\Synchronizer\Process\Jobs\LoadAllAndParseJob::class,
            \Grixu\Synchronizer\Process\Jobs\SyncParsedDataJob::class,
        ],
        'chunk-load-and-parse' => [
            \Grixu\Synchronizer\Process\Jobs\ChunkLoadAndParseJob::class,
            \Grixu\Synchronizer\Process\Jobs\SyncParsedDataJob::class,
        ],
        'chunk-rest-parse' => [
            \Grixu\Synchronizer\Process\Jobs\ChunkRestParseJob::class,
            \Grixu\Synchronizer\Process\Jobs\SyncParsedDataJob::class,
        ],
    ],

    'queues' => [
        'notifications' => env('SYNCHRONIZER_NOTIFICATION_QUEUE', 'notifications'),
        'release' => env('SYNCHRONIZER_QUEUE_RELEASE', 1),
    ],

    'handlers' => [
        'error' => \Grixu\Synchronizer\Tests\Helpers\FakeErrorHandler::class,
        'sync' => \Grixu\Synchronizer\Tests\Helpers\FakeSyncHandler::class
    ],
];

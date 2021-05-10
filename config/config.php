<?php

return [
    'sync' => [
        'timestamps' => [
            'created_at',
            'updated_at'
        ],

        'default_chunk_size' => env('SYNCHRONIZER_CHUNK_SIZE', 250),
    ],

    'checksum' => [
        'control' => env('SYNCHRONIZER_CHECKSUM_CONTROL', true),
        'field' => env('SYNCHRONIZER_CHECKSUM_FIELD', 'checksum'),
        'timestamps_excluded' => false,
    ],

    'logger' => [
        'db' => env('SYNCHRONIZER_DB_LOGGING', true),

        'notifications' => [
            'slack' => env('SYNCHRONIZER_SLACK_WEBHOOK', null),
        ]
    ],

    'jobs' => [
        'default' => [
            \Grixu\Synchronizer\Jobs\LoadDataToSyncJob::class,
            \Grixu\Synchronizer\Jobs\ParseLoadedDataJob::class,
            \Grixu\Synchronizer\Jobs\SyncParsedDataJob::class
        ],
        'load-all-and-parse' => [
            \Grixu\Synchronizer\Jobs\LoadAllAndParseJob::class,
            \Grixu\Synchronizer\Jobs\SyncParsedDataJob::class
        ],
        'chunk-load-and-parse' => [
            \Grixu\Synchronizer\Jobs\ChunkLoadAndParseJob::class,
            \Grixu\Synchronizer\Jobs\SyncParsedDataJob::class
        ]
    ],

//    'handlers' => [
//        'error' => \Grixu\Synchronizer\Tests\Helpers\FakeErrorHandler::class,
//        'sync' => \Grixu\Synchronizer\Tests\Helpers\FakeSyncHandler::class
//    ],
];

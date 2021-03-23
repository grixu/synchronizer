<?php

return [
    'sync' => [
        'send_notification' => env('SYNCHRONIZER_SLACK_SUM_UP', false),
        'logging' => env('SYNCHRONIZER_DB_LOGGING',true),

        'timestamps' => [
            'updatedAt'
        ],

        'default_chunk_size' => env('SYNCHRONIZER_CHUNK_SIZE', 250),
    ],

    'checksum' => [
        'control' => env('SYNCHRONIZER_MD5_CONTROL', true),
        'field' => env('SYNCHRONIZER_MD5_FIELD', 'checksum'),
        'timestamps_excluded' => false,
    ],

//    'handlers' => [
//        'error' => \Grixu\Synchronizer\Tests\Helpers\FakeErrorHandler::class,
//        'sync' => \Grixu\Synchronizer\Tests\Helpers\FakeSyncHandler::class
//    ],
];

<?php

return [
    'send_slack_sum_up' => env('SYNCHRONIZER_SLACK_SUM_UP', false),
    'db_logging' => env('SYNCHRONIZER_DB_LOGGING',true),
    'timestamps' => [
        'updatedAt'
    ],
    'checksum_control' => env('SYNCHRONIZER_MD5_CONTROL', true),
    'checksum_field' => env('SYNCHRONIZER_MD5_FIELD', 'checksum'),
];

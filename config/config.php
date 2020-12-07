<?php

return [
    'send_slack_sum_up' => env('SYNCHRONIZER_SLACK_SUM_UP', true),
    'db_logging' => env('SYNCHRONIZER_DB_LOGGING',true),
    'log_turned_off_fields' => [
        'updated_at'
    ],
    'md5_control' => env('SYNCHRONIZER_MD5_CONTROL', true),
    'md5_local_model_field' => env('SYNCHRONIZER_MD5_FIELD', 'checksum'),
];

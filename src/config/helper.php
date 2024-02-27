<?php
return [
    'paginate' => [
        'page_max' => 30,
        'limit_max'=> 100
    ],
    'deploy' => [
        'commands' => [
            [
                'enable' => true,
                'description' => 'Update code',
                'folder' => '/var/www/html/code/', //folder run command
                'cmd' => [
                    'git pull origin master',
                    'composer update --no-dev'
                ],
                'user' => '',//User run command (default user ssh)
                'ask' => false
            ]
        ],
        'servers' => [
            [
                'enable' => true,
                'name' => 'ai_master',
                'ip' => '127.0.0.1',
                'user_name' => 'ubuntu'
            ]
        ]
    ],
    'log' => [
        'driver' => env("HELPER_LOG_DRIVER", 'slack'),
        'enable' => env("HELPER_LOG_ENABLE", true),
        'connections' => [
            'slack' => [
                'name' => env('HELPER_LOG_QUEUE_NAME', 'send-log-slack'),
                'slack_error_url' => env("SLACK_ERROR_URL"),
                'slack_log_url'=>env("SLACK_LOG_URL"),
            ],
        ]
    ]
];
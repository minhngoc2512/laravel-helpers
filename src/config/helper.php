<?php
return [
    'paginate' => [
        'page_max' => 30,
        'limit_max' => 100
    ],
    'backup' => [
        'enable' => true,
//        'ask' => false,
        'backup_driver' => 'local',
        'database' => [
            'folder' => '/var/www/html/backup/database',
            'description' => 'Backup database',
            'file_name' => 'database_backup.sql',
            'file_config' => '/home/ubuntu/mysql_config.conf',
            'database_name' => 'database',
            'tables' => [
                'table1',
                'table2'
            ],
        ],
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
    'log_query' => env('HELPER_LOG_QUERY', false),
    'log' => [
        'driver' => env("HELPER_LOG_DRIVER", 'slack'),
        'enable' => env("HELPER_LOG_ENABLE", true),
        'name_queue' => env('HELPER_LOG_QUEUE_NAME', 'send-log'),
        'connections' => [
            'slack' => [
                'name' => 'Send Log To Slack',
                'slack_error_url' => env("SLACK_ERROR_URL"),
                'slack_log_url' => env("SLACK_LOG_URL"),
            ],
        ]
    ]
];
### 1. Cài đặt
```bash
composer require ngocnm/laravel_helpers
```
### 2. Cấu hình với laravel
- Register Service Provider

```php
\Ngocnm\LaravelHelpers\providers\HelperServiceProvider::class
```

- Thêm ``middleware`` trong group ``api`` trong file ``app/Http/Kernel.php``:
```php
protected $middlewareGroups = [
    ...,
    'api' => [
        ...,
        FilterRequestForApi::class
    ],
];
```

- Để publish file config chạy lệnh bên dưới:
```bash 
php artisan vendor:publish --tag=helper_config
# Vị trí file config  config/helper.php
```
- ``config/helper.php``
```php 
<?php
return [
    'paginate' => [
        'page_max' => 30,
        'limit_max' => 100
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
        ],
    ],
    'log' => [
        'driver' => env("HELPER_LOG_DRIVER", 'slack'),
        'enable' => env("HELPER_LOG_ENABLE", true),
        'connections' => [
            'slack' => [
                'name' => 'Send Message To Slack',
                'slack_error_url' => env("SLACK_ERROR_URL"),
                'slack_log_url'=>env("SLACK_LOG_URL"),
            ],
        ]
    ]
];

```
- Cấu hình env:
```dotenv
SLACK_ERROR_URL=
SLACK_LOG_URL=
HELPER_LOG_DRIVER=
HELPER_LOG_ENABLE=
HELPER_LOG_QUEUE_NAME=
IP_SERVER=
APP_NAME=
```
- Thêm vào config/app.php:
```php
    'ip_server' => env('IP_SERVER', 'localhost'),
    'app_name' => env('APP_NAME0', 'localhost'),
```

- Cấu hình trong class ``model``

```php 
namespace App\Models;

class User {
    static $schema = [
        "id" => [
            "type" => "int",
            "insert" => false,
            "query_condition" => true,
            "sort" => true
        ],
        "title" => [
            "type" => "string",
            "insert" => false,
            "query_condition" => true,
            "sort" => true,
            "fulltext_search" => true
        ],
        "created_at" => [
            "type" => "string",
            "insert" => false,
            "query_condition" => false,
            "required_when_create" => false,
            "sort" => true
        ],
        "updated_at" => [
            "type" => "string",
            "insert" => false,
            "query_condition" => false,
            "required_when_create" => false,
            "sort" => true
        ]
    ];
}
```
- Nếu this->app->singleton không hoạt động thì thêm vào Exceptions/Handler:
```php
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            \Ngocnm\LaravelHelpers\exceptions\SendLog::SendLog($e);
        }
    }
```

Mô tả :
-  ``type``: Kiểu dữ liệu, bao gồm: ``int``,``string``,``double``
- ``insert``: Có được thêm dữ liệu từ request param hay không
- ``query_condition``: Truy vấn có điều khiện 
- ``required_when_create``: Yêu cầu bắt buộc với validate request
- ``sort``: Truy vấn ``order by``,
- ``fulltext_search``: Tìm kiếm bằng fulltext search trong [mysql](https://dev.mysql.com/doc/refman/8.0/en/fulltext-search.html)
- Lấy tham số từ request:
```php
Ngocnm\LaravelHelpers\Helper::BaseApiRequest()->getFields();
Ngocnm\LaravelHelpers\Helper::BaseApiRequest()->getFields();
...
```
- Đăng ký với Model:
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Ngocnm\LaravelHelpers\eloquent\BaseModel; // add line

class UserLog extends Model
{
    use HasFactory,BaseModel;// add line
    protected $table = 'user_logs';
}
```
## 3. Khai báo trong hàm lấy dữ liệu:
```php 

// Filter request 
function getUserBy Api(){
    $model = UserLog::baseQueryBuilder(UserLog::class);//add line
    ...
    return $model->get();
}
```
## 4. Các tham số lấy dữ liệu từ api
- ``select`` : ``fields=field_1,field_2,field_3,...``
- ``where`` : ``where=field_1+condition_1,field_2+condition_2,...``
- ``where_not`` : ``where_not=field_1+condition_1,field_2+condition_2,...``
- ``where_in`` : ``where_in=field_1+condition_1,field_2+condition_2,...`` hoặc ``where_in[]=field_1+condition_1,field_2+condition_2&where_in[]=field_2+condition_3,field_3+condition_4``
- ``limit`` : ``limit=30`` (mặc định là 30)
- ``page`` : ``page=1`` (mặc định là 1)
- ``ofset``: ``offset=30`` (mặc định sẽ tính từ tham số ``limit`` và ``page``)
- ``order_by`` : ``order_by=field_1+desc,field_2+asc,...``
- ``field_search``: ``field_search=column_search`` (Trường tìm kiếm, đi kèm với ``field_search``)
- ``keyword`` : ``keyword=something`` (Từ khóa tìm kiếm, đi kèm với ``keyword``)
- ``with`` : ``with=relashtionship_1+field_1,field_2-relashtionship_2+field_1,field_2``

``Lưu ý``: Để dùng được param ``with`` cần khai báo quan hệ trong class Model:

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Ngocnm\LaravelHelpers\eloquent\BaseModel;

class UserLog extends Model
{
    use HasFactory,BaseModel;
    protected $table = 'user_logs';
    
    const relationship_device_fields = ['id','name','ip']; // add line
    
    public function device(){ // add function
        return $this->belongsTo(Device::class,  'device_id','id');
    }
}
```

## 5. Tùy chỉnh
- Tùy chỉnh cấu hình ``limit_max`` , ``page_max``;

```php
Ngocnm\LaravelHelpers\Helper::BaseApiRequest()->setLimitMax(100);
Ngocnm\LaravelHelpers\Helper::BaseApiRequest()->setPageMax(100);
```
## 6. Tự động pull code , run command trên nhiều server
- Tùy chình tham số ``deploy`` trong file ``config/helper.php``
- Thêm ssh key remote giữa các ``server master`` và các ``server cluster``
```bash 
php artisan helper:deploy-app
```
## 7. Log query cho api
- Cấu hình ``env``:
```dotenv
HELPER_QUERY_LOG=true
```
- Thêm middleware ``api``:
```php
protected $middlewareGroups = [
    ...,
    'api' => [
        ...,
        LogQueryForApi::class
    ],
];
```
- Response api sẽ trả về bao gồm tham số ``log_query``:
```json
{
  "data": {},
  "query_log": {}
}
```

## 8. Cấu hình job Send Message To Slack cho server
- Cấu hình supervisor:
```bash
cd /etc/supervisor/conf.d
vim send-log.conf
```
- Copy đoạn cấu hình:
```bash
[program:send-log]
process_name=%(program_name)s_%(process_num)02d
command=/usr/bin/php /path/to/artisan queue:work --queue=send-log --sleep=3  --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=eztech
numprocs=1
redirect_stderr=true
stdout_logfile=/var/log/supervisor/send-log.log
stopwaitsecs=3600
```
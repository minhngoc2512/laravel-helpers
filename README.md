### Cài đặt
```bash
composer require ngocnm/laravel_helpers
```
### Cấu hình 
- Register Service Provider
```php
Ngocnm\LaravelHelpers\HelperServiceProvider::class
```
- Publish config file config/helper.php
```php 
php artisan vendor:publish --tag=helper_config
```

### Cấu hình trong ``model``

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

Note:
-  ``type``: Kiểu dữ liệu, bao gồm: ``int``,``string``,``double``
- ``insert``: Có được thêm dữ liệu từ request param hay không
- ``query_condition``: Truy vấn có điều khiện 
- ``required_when_create``: Yêu cầu bắt buộc với validate request
- ``sort``: Truy vấn ``order by``

### Lọc request cho api
- Trong file ``app/Http/Kernel.php``, thêm middleware cho api:
```php
use Ngocnm\LaravelHelpers\middleware\FilterRequestForApi;
...

    protected $middlewareGroups = [
        ...
        'api' => [
            ...,
            FilterRequestForApi::class
        ],
    ];

```
- Lấy tham số từ request:
```php
Ngocnm\LaravelHelpers\Helper::BaseApiRequest()->getFields();
Ngocnm\LaravelHelpers\Helper::BaseApiRequest()->getFields();
...
```
- Dùng cho ``eloquent``:
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

// Filter request 
$model = UserLog::baseQueryBuilder(UserLog::class);//add line
...
$model = $model->get();
```

- Cấu hình ``limit_max`` , ``page_max``;

```php
Ngocnm\LaravelHelpers\Helper::BaseApiRequest()->setLimitMax(100);
Ngocnm\LaravelHelpers\Helper::BaseApiRequest()->setPageMax(100);
```
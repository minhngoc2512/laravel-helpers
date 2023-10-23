### 1. Cài đặt
```bash
composer require ngocnm/laravel_helpers
```
### 2. Cấu hình với laravel
- Register Service Provider
```php
Ngocnm\LaravelHelpers\HelperServiceProvider::class
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
- ``limit`` : ``limit=30`` (mặc định là 30)
- ``page`` : ``page=1`` (mặc định là 1)
- ``ofset``: ``offset=30`` (mặc định sẽ tính từ tham số ``limit`` và ``page``)
- ``order_by`` : ``order_by=column_1+desc,column_2+asc,...``
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

## Tùy chỉnh
- Tùy chỉnh cấu hình ``limit_max`` , ``page_max``;

```php
Ngocnm\LaravelHelpers\Helper::BaseApiRequest()->setLimitMax(100);
Ngocnm\LaravelHelpers\Helper::BaseApiRequest()->setPageMax(100);
```
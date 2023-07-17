### Cài đặt
```bash
composer require ngocnm/laravel_helpers
```
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

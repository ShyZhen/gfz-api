### 初始化登录账号
 u: admin
 p: admin

### 创建控制器
// Mac os、 Linux
php artisan admin:make UserController --model=App\\User

// Windows
php artisan admin:make UserController --model=App\User

### 创建路由
$router->resource('users', UserController::class);


### 去后台界面添加路由

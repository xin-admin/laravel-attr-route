# Laravel Attribute Route

[English](README.md)

使用 PHP 8 注解（Attributes）注册 Laravel 路由与 Sanctum 验证的轻量级组件（支持多用户模型）

在控制器上通过注解声明路由、中间件与权限，无需再手动维护路由文件：

```php
<?php

namespace App\Http\Controllers;

use Xin\AttrRoute\Attribute\GetRoute;
use Xin\AttrRoute\Attribute\PostRoute;
use Xin\AttrRoute\Attribute\RequestAttribute;

#[RequestAttribute(routePrefix: '/admin/user', abilitiesPrefix: 'admin')]
class UserController
{
    // GET /admin/user/list，需要登录态与 admin.user.list 权限
    #[GetRoute(route: '/list', authorize: 'user.list')]
    public function list()
    {
        // ...
    }

    // POST /admin/user/create，跳过鉴权，加限流中间件
    #[PostRoute(route: '/create', authorize: false, middleware: 'throttle:10')]
    public function create()
    {
        // ...
    }
}
```

## 环境要求

- PHP >= 8.2（Laravel 13 需要 PHP >= 8.3）
- Laravel 11 / 12 / 13

## 安装

```bash
composer require xinadmin/laravel-attr-route
```

发布配置文件：

```bash
php artisan vendor:publish --tag=attr-route
```

## 快速开始

默认情况下，ServiceProvider 会自动扫描 `app/Http/Controllers` 并注册所有注解路由。
扫描目录可在 `config/attr-route.php` 的 `scan_paths` 中修改：

```php
'scan_paths' => [
    app_path('Http/Controllers'),
],
```

也可以关闭自动扫描（`'auto_scan' => false`），在你自己的 ServiceProvider 中手动注册：

```php
use Xin\AttrRoute\Contracts\AttrRoute;

public function boot(AttrRoute $attrRoute): void
{
    $attrRoute->register(app_path('Http/Controllers'));
    // 支持数组
    $attrRoute->register([$pathA, $pathB]);
}
```

路由缓存后（`php artisan route:cache`）会自动跳过扫描，因为缓存中已包含注解路由。

## 注解说明

### 类注解 `RequestAttribute`

标注在控制器类上，是所有路由注解生效的前提：

| 参数                | 类型              | 说明                             |
|-------------------|-----------------|--------------------------------|
| `routePrefix`     | `string`        | 路由前缀，如 `/admin/user`           |
| `abilitiesPrefix` | `string`        | 权限【能力】前缀，与方法注解的 `authorize` 拼接 |
| `middleware`      | `string\|array` | 控制器级中间件，作用于类下所有路由              |
| `authModel`       | `?string`       | 用户模型守卫别名，验证 token 属于的模型       |

### 方法注解

| 注解               | HTTP 方法 |
|------------------|---------|
| `#[GetRoute]`    | GET     |
| `#[PostRoute]`   | POST    |
| `#[PutRoute]`    | PUT     |
| `#[PatchRoute]`  | PATCH   |
| `#[DeleteRoute]` | DELETE  |
| `#[AnyRoute]`    | 所有方法    |

方法注解的公共参数：

| 参数           | 类型              | 默认值    | 说明                              |
|--------------|-----------------|--------|---------------------------------|
| `route`      | `string`        | `''`   | 路由地址，与 `routePrefix` 拼接         |
| `authorize`  | `string\|bool`  | `true` | `false` 关闭鉴权；字符串时作为权限名与前缀拼接     |
| `middleware` | `string\|array` | `''`   | 路由级中间件                          |
| `where`      | `array`         | `[]`   | 路由参数正则约束，如 `['id' => '[0-9]+']` |

## 模型守卫（多用户表认证）

`authModel` 是一个**别名**，通过 `config('attr-route.models')` 解析为具体的模型类。例如：

```php
// config/attr-route.php
'models' => [
    'default' => App\Models\User::class,
    'admin'   => App\Models\Admin::class,
],
```

```php
// 仅允许签发对象为 App\Models\Admin 的 token 访问
#[RequestAttribute(routePrefix: '/admin', authModel: 'admin')]
class AdminController { /* ... */ }
```

省略 `authModel` 时使用 `default` 别名。解析出的模型类为空时，**不校验** token 所属模型，请求直接放行。

## 配置文件

```php
<?php

return [

    /*
    |--------------------------------------------------------------------------
    | 自动扫描
    |--------------------------------------------------------------------------
    |
    | 开启后，ServiceProvider 会在应用启动时自动扫描 scan_paths 中带有
    | 路由注解的控制器并注册路由。也可以关闭后手动调用：
    | app(AttrRoute::class)->register($path);
    |
    */
    'auto_scan' => true,

    /*
    |--------------------------------------------------------------------------
    | 扫描路径
    |--------------------------------------------------------------------------
    |
    | 注解路由的控制器扫描目录，会递归查找其中的 *Controller.php 文件。
    |
    */
    'scan_paths' => [
        app_path('Http/Controllers'),
    ],

    /*
    |--------------------------------------------------------------------------
    | 用户模型
    |--------------------------------------------------------------------------
    |
    | 用户模型配置（别名 => 模型类），在多用户表的模式下，用于验证具体令牌
    | 属于那个模型。default 为空时不验证令牌所属的模型
    |
    */
    'models' => [
        'default' => '',
    ]
];
```

## License

MIT

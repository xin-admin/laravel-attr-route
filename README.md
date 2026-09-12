# Laravel Attribute Route

[中文文档](README.zh-CN.md)

A lightweight package that registers Laravel routes and Sanctum authentication using PHP 8 Attributes (supports multiple user models).

Declare routes, middleware and permissions directly on controllers — no more maintaining route files by hand:

```php
<?php

namespace App\Http\Controllers;

use Xin\AttrRoute\Attribute\GetRoute;
use Xin\AttrRoute\Attribute\PostRoute;
use Xin\AttrRoute\Attribute\RequestAttribute;

#[RequestAttribute(routePrefix: '/admin/user', abilitiesPrefix: 'admin')]
class UserController
{
    // GET /admin/user/list, requires authentication and the admin.user.list ability
    #[GetRoute(route: '/list', authorize: 'user.list')]
    public function list()
    {
        // ...
    }

    // POST /admin/user/create, skips authorization, with a throttle middleware
    #[PostRoute(route: '/create', authorize: false, middleware: 'throttle:10')]
    public function create()
    {
        // ...
    }
}
```

## Requirements

- PHP >= 8.2 (Laravel 13 requires PHP >= 8.3)
- Laravel 11 / 12 / 13

## Installation

```bash
composer require xinadmin/laravel-attr-route
```

Publish the configuration file:

```bash
php artisan vendor:publish --tag=attr-route
```

## Quick Start

By default, the ServiceProvider automatically scans `app/Http/Controllers` and registers all attribute routes.
You can change the scan directories via `scan_paths` in `config/attr-route.php`:

```php
'scan_paths' => [
    app_path('Http/Controllers'),
],
```

You can also disable auto scanning (`'auto_scan' => false`) and register manually in your own ServiceProvider:

```php
use Xin\AttrRoute\Contracts\AttrRoute;

public function boot(AttrRoute $attrRoute): void
{
    $attrRoute->register(app_path('Http/Controllers'));
    // Arrays are supported
    $attrRoute->register([$pathA, $pathB]);
}
```

When routes are cached (`php artisan route:cache`), scanning is skipped automatically — the cached routes already contain the attribute routes.

## Attributes

### Class attribute `RequestAttribute`

Must be declared on the controller class — it is the prerequisite for all route attributes to take effect:

| Parameter         | Type            | Description                                                       |
|-------------------|-----------------|-------------------------------------------------------------------|
| `routePrefix`     | `string`        | Route prefix, e.g. `/admin/user`                                  |
| `abilitiesPrefix` | `string`        | Ability prefix, concatenated with the method attribute `authorize` |
| `middleware`      | `string\|array` | Controller-level middleware, applied to all routes of the class   |
| `authModel`       | `?string`       | User model guard alias, verifies which model the token belongs to |

### Method attributes

| Attribute        | HTTP Method |
|------------------|-------------|
| `#[GetRoute]`    | GET         |
| `#[PostRoute]`   | POST        |
| `#[PutRoute]`    | PUT         |
| `#[PatchRoute]`  | PATCH       |
| `#[DeleteRoute]` | DELETE      |
| `#[AnyRoute]`    | All methods |

Common parameters of method attributes:

| Parameter    | Type            | Default | Description                                                       |
|--------------|-----------------|---------|-------------------------------------------------------------------|
| `route`      | `string`        | `''`    | Route path, concatenated with `routePrefix`                       |
| `authorize`  | `string\|bool`  | `true`  | `false` disables authorization; a string is used as the ability name concatenated with the prefix |
| `middleware` | `string\|array` | `''`    | Route-level middleware                                            |
| `where`      | `array`         | `[]`    | Regex constraints for route parameters, e.g. `['id' => '[0-9]+']` |

## Model Guard (Multi-User-Table Authentication)

`authModel` is an **alias** that is resolved against `config('attr-route.models')`. For example:

```php
// config/attr-route.php
'models' => [
    'default' => App\Models\User::class,
    'admin'   => App\Models\Admin::class,
],
```

```php
// Tokens issued to App\Models\Admin only
#[RequestAttribute(routePrefix: '/admin', authModel: 'admin')]
class AdminController { /* ... */ }
```

When `authModel` is omitted, the `default` alias is used. If the resolved model class is empty, the token's model is **not** verified and the request passes through directly.

## Configuration File

```php
<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Auto Scan
    |--------------------------------------------------------------------------
    |
    | When enabled, the ServiceProvider scans scan_paths on application boot
    | and registers controllers carrying route attributes. You can also
    | disable it and call manually:
    | app(AttrRoute::class)->register($path);
    |
    */
    'auto_scan' => true,

    /*
    |--------------------------------------------------------------------------
    | Scan Paths
    |--------------------------------------------------------------------------
    |
    | Controller directories scanned for attribute routes. The scanner
    | recursively looks for *Controller.php files.
    |
    */
    'scan_paths' => [
        app_path('Http/Controllers'),
    ],

    /*
    |--------------------------------------------------------------------------
    | User Models
    |--------------------------------------------------------------------------
    |
    | User model map keyed by alias. In multi-user-table mode it verifies
    | which model a token belongs to. When `default` is empty, the token's
    | model is not verified.
    |
    */
    'models' => [
        'default' => '',
    ]
];
```

## License

MIT

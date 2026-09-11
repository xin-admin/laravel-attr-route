<?php

namespace Xin\AnnoRoute\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static void register(string|array $path) 从指定路径扫描控制器并注册注解路由
 *
 * @see \Xin\AnnoRoute\AttrRouteService
 */
class AttrRoute extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Xin\AnnoRoute\Contracts\AttrRoute::class;
    }
}

<?php

namespace Xin\AttrRoute\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static void register(string|array $path) 从指定路径扫描控制器并注册注解路由
 *
 * @see \Xin\AttrRoute\AttrRouteService
 */
class AttrRoute extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Xin\AttrRoute\Contracts\AttrRoute::class;
    }
}

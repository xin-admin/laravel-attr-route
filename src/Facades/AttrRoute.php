<?php

namespace Xin\AttrRoute\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static void register(string|array $path) Scan controllers from the given paths and register attribute routes
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

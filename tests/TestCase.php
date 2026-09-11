<?php

namespace Xin\AnnoRoute\Tests;

use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Route as RouteFacade;
use Orchestra\Testbench\TestCase as Orchestra;
use Xin\AnnoRoute\AttrRouteServiceProvider;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app)
    {
        return [AttrRouteServiceProvider::class];
    }

    protected function getEnvironmentSetUp($app)
    {
        // 默认清空扫描路径，避免各用例间相互影响；
        // 需要测试自动扫描的用例使用 AutoScanTestCase
        $app['config']->set('attr-route.scan_paths', []);
    }

    /**
     * 在已注册的路由中按 URI 与 HTTP 方法查找路由
     */
    protected function findRoute(string $uri, string $method = 'GET'): ?Route
    {
        $uri = ltrim($uri, '/');

        foreach (RouteFacade::getRoutes() as $route) {
            if ($route->uri() === $uri && in_array($method, $route->methods(), true)) {
                return $route;
            }
        }

        return null;
    }
}

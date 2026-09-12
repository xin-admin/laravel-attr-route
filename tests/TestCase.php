<?php

namespace Xin\AttrRoute\Tests;

use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Route as RouteFacade;
use Orchestra\Testbench\TestCase as Orchestra;
use Xin\AttrRoute\AttrRouteServiceProvider;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [AttrRouteServiceProvider::class];
    }

    protected function getEnvironmentSetUp($app): void
    {
        // 默认清空扫描路径，避免 Provider 启动时自动扫描影响各用例；
        // 需要验证自动扫描的用例使用 AutoScanTestCase
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

    /**
     * 已注册路由的 URI 列表（便于断言）
     *
     * @return string[]
     */
    protected function registeredUris(): array
    {
        return collect(RouteFacade::getRoutes()->getRoutes())
            ->map(fn (Route $route) => $route->uri())
            ->values()
            ->all();
    }
}

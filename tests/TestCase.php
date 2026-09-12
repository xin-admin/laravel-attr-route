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
        // Clear scan paths by default so the provider does not auto-scan during boot;
        // use AutoScanTestCase for cases that need auto scanning
        $app['config']->set('attr-route.scan_paths', []);
    }

    /**
     * Find a registered route by URI and HTTP method
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
     * List of registered route URIs (for assertions)
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

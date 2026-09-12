<?php

namespace Xin\AttrRoute;

use Illuminate\Support\ServiceProvider;
use Xin\AttrRoute\Contracts\AttrRoute;

class AttrRouteServiceProvider extends ServiceProvider
{
    /**
     * Register services
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/attr-route.php', 'attr-route');

        $this->app->singleton(AttrRoute::class, AttrRouteService::class);
    }

    /**
     * Bootstrap services
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            // Publish the configuration file
            $this->publishes([
                __DIR__ . '/../config/attr-route.php' => config_path('attr-route.php'),
            ], 'attr-route');
        }

        $this->registerAttributeRoutes();
    }

    /**
     * Auto scan and register attribute routes
     */
    protected function registerAttributeRoutes(): void
    {
        if (! config('attr-route.auto_scan', true)) {
            return;
        }

        // No need to scan again when routes are cached; the cache already contains attribute routes
        if ($this->app->routesAreCached()) {
            return;
        }

        $paths = array_filter((array) config('attr-route.scan_paths', []));
        if (empty($paths)) {
            return;
        }

        $this->app->make(AttrRoute::class)->register($paths);
    }
}

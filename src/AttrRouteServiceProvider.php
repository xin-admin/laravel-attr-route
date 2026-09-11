<?php

namespace Xin\AnnoRoute;

use Illuminate\Support\ServiceProvider;
use Xin\AnnoRoute\Contracts\AttrRoute;

class AttrRouteServiceProvider extends ServiceProvider
{
    /**
     * 注册服务
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/attr-route.php', 'attr-route');

        $this->app->singleton(AttrRoute::class, AttrRouteService::class);
    }

    /**
     * 启动服务
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            // 发布配置文件
            $this->publishes([
                __DIR__ . '/../config/attr-route.php' => config_path('attr-route.php'),
            ], 'attr-route');
        }

        $this->registerAttributeRoutes();
    }

    /**
     * 自动扫描并注册注解路由
     */
    protected function registerAttributeRoutes(): void
    {
        if (! config('attr-route.auto_scan', true)) {
            return;
        }

        // 路由已缓存时无需重复扫描，缓存中已包含注解路由
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

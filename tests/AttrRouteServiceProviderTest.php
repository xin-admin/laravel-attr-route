<?php

namespace Xin\AttrRoute\Tests;

use Illuminate\Support\Facades\Route as RouteFacade;
use Xin\AttrRoute\AttrRouteService;
use Xin\AttrRoute\Contracts\AttrRoute;
use Xin\AttrRoute\Facades\AttrRoute as AttrRouteFacade;

class AttrRouteServiceProviderTest extends TestCase
{
    public function test_contract_is_bound_as_singleton(): void
    {
        $this->assertInstanceOf(AttrRouteService::class, app(AttrRoute::class));
        $this->assertSame(app(AttrRoute::class), app(AttrRoute::class));
    }

    public function test_config_is_merged(): void
    {
        $this->assertTrue(config('attr-route.auto_scan'));
        $this->assertIsArray(config('attr-route.models'));
        $this->assertArrayHasKey('default', config('attr-route.models'));
        $this->assertArrayHasKey('output', config('attr-route.helper'));
    }

    public function test_facade_resolves_to_service(): void
    {
        $this->assertInstanceOf(AttrRouteService::class, AttrRouteFacade::getFacadeRoot());
    }

    public function test_no_routes_registered_when_scan_paths_empty(): void
    {
        // 基础 TestCase 将 scan_paths 置空，启动时不应注册任何路由
        $this->assertCount(0, RouteFacade::getRoutes()->getRoutes());
    }
}

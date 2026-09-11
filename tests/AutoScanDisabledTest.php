<?php

namespace Xin\AttrRoute\Tests;

use Illuminate\Support\Facades\Route as RouteFacade;

/**
 * 验证 auto_scan = false 时即使配置了 scan_paths 也不会注册路由。
 */
class AutoScanDisabledTest extends AutoScanDisabledTestCase
{
    public function test_no_routes_registered_when_auto_scan_disabled(): void
    {
        $this->assertCount(0, RouteFacade::getRoutes()->getRoutes());
    }
}

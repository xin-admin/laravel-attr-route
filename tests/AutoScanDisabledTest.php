<?php

namespace Xin\AttrRoute\Tests;

use Illuminate\Support\Facades\Route as RouteFacade;

/**
 * Verify no routes are registered when auto_scan = false, even with scan_paths configured.
 */
class AutoScanDisabledTest extends AutoScanDisabledTestCase
{
    public function test_no_routes_registered_when_auto_scan_disabled(): void
    {
        $this->assertCount(0, RouteFacade::getRoutes()->getRoutes());
    }
}

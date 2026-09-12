<?php

namespace Xin\AttrRoute\Tests;

/**
 * Verify the ServiceProvider auto-scans scan_paths and registers attribute routes on boot.
 */
class AutoScanTest extends AutoScanTestCase
{
    public function test_routes_are_registered_automatically_on_boot(): void
    {
        // No manual register call — the provider scanned the Fixtures directory on boot
        $this->assertNotNull($this->findRoute('users/list', 'GET'));
        $this->assertNotNull($this->findRoute('admin/dashboard', 'GET'));
    }

    public function test_auto_scanned_route_is_reachable_over_http(): void
    {
        $this->get('/users/list')->assertOk()->assertSee('user.list');
    }
}

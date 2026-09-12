<?php

namespace Xin\AttrRoute\Tests;

/**
 * 验证 ServiceProvider 启动时自动扫描 scan_paths 并注册注解路由。
 */
class AutoScanTest extends AutoScanTestCase
{
    public function test_routes_are_registered_automatically_on_boot(): void
    {
        // 无需手动调用 register，Provider boot 时已扫描 Fixtures 目录
        $this->assertNotNull($this->findRoute('users/list', 'GET'));
        $this->assertNotNull($this->findRoute('admin/dashboard', 'GET'));
    }

    public function test_auto_scanned_route_is_reachable_over_http(): void
    {
        $this->get('/users/list')->assertOk()->assertSee('user.list');
    }
}

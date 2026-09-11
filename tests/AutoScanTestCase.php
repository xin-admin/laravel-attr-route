<?php

namespace Xin\AttrRoute\Tests;

/**
 * 开启自动扫描并指向 Fixtures 目录的测试基类，
 * 用于验证 ServiceProvider 启动时自动注册注解路由。
 */
abstract class AutoScanTestCase extends TestCase
{
    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('attr-route.auto_scan', true);
        $app['config']->set('attr-route.scan_paths', [__DIR__ . '/Fixtures']);
    }
}

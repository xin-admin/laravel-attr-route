<?php

namespace Xin\AttrRoute\Tests;

/**
 * 关闭自动扫描但配置了扫描路径的测试基类，
 * 用于验证 auto_scan = false 时不会注册任何路由。
 */
abstract class AutoScanDisabledTestCase extends TestCase
{
    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('attr-route.auto_scan', false);
        $app['config']->set('attr-route.scan_paths', [__DIR__ . '/Fixtures']);
    }
}

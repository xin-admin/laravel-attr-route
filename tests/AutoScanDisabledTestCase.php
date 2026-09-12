<?php

namespace Xin\AttrRoute\Tests;

/**
 * Base test case with auto scan disabled but scan paths configured,
 * used to verify no routes are registered when auto_scan = false.
 */
abstract class AutoScanDisabledTestCase extends TestCase
{
    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('attr-route.auto_scan', false);
        $app['config']->set('attr-route.scan_paths', [__DIR__ . '/Fixtures']);
    }
}

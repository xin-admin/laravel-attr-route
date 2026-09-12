<?php

namespace Xin\AttrRoute\Tests;

/**
 * Base test case that enables auto scanning pointed at the Fixtures directory,
 * used to verify the ServiceProvider registers attribute routes on boot.
 */
abstract class AutoScanTestCase extends TestCase
{
    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('attr-route.auto_scan', true);
        $app['config']->set('attr-route.scan_paths', [__DIR__ . '/Fixtures']);
    }
}

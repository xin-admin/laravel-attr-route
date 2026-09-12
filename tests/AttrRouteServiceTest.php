<?php

namespace Xin\AttrRoute\Tests;

use Illuminate\Support\Facades\Route as RouteFacade;
use Xin\AttrRoute\AttrRouteService;
use Xin\AttrRoute\Contracts\AttrRoute;
use Xin\AttrRoute\Facades\AttrRoute as AttrRouteFacade;

class AttrRouteServiceTest extends TestCase
{
    public function test_register_scans_directory_and_registers_controllers(): void
    {
        (new AttrRouteService())->register(__DIR__ . '/Fixtures');

        // Routes from both UserController and AdminController are registered
        $this->assertNotNull($this->findRoute('users/list', 'GET'));
        $this->assertNotNull($this->findRoute('admin/dashboard', 'GET'));

        // Controllers without RequestAttribute are not registered
        $this->assertNull($this->findRoute('plain', 'GET'));
    }

    public function test_register_accepts_multiple_paths(): void
    {
        (new AttrRouteService())->register([
            __DIR__ . '/Fixtures',
            __DIR__ . '/Fixtures/DoesNotExist',
        ]);

        $this->assertNotNull($this->findRoute('users/list', 'GET'));
    }

    public function test_register_ignores_missing_directory(): void
    {
        (new AttrRouteService())->register(__DIR__ . '/no-such-dir');

        $this->assertCount(0, RouteFacade::getRoutes()->getRoutes());
    }

    public function test_register_only_scans_controller_suffix_files(): void
    {
        $dir = sys_get_temp_dir() . '/attr-route-' . uniqid();
        mkdir($dir);
        // Files not matching *Controller.php must not be scanned
        file_put_contents(
            $dir . '/Helper.php',
            "<?php\nnamespace AttrRouteTemp;\nclass Helper {}\n"
        );

        try {
            (new AttrRouteService())->register($dir);

            $this->assertCount(0, RouteFacade::getRoutes()->getRoutes());
        } finally {
            unlink($dir . '/Helper.php');
            rmdir($dir);
        }
    }

    public function test_register_skips_files_without_loadable_class(): void
    {
        $dir = sys_get_temp_dir() . '/attr-route-' . uniqid();
        mkdir($dir);
        // A file without a namespace cannot be mapped to a loadable class and is silently skipped
        file_put_contents(
            $dir . '/NoNamespaceController.php',
            "<?php\nclass NoNamespaceController {}\n"
        );

        try {
            (new AttrRouteService())->register($dir);

            $this->assertCount(0, RouteFacade::getRoutes()->getRoutes());
        } finally {
            unlink($dir . '/NoNamespaceController.php');
            rmdir($dir);
        }
    }

    public function test_register_via_container_contract(): void
    {
        app(AttrRoute::class)->register(__DIR__ . '/Fixtures');

        $this->assertNotNull($this->findRoute('users/list', 'GET'));
    }

    public function test_register_via_facade(): void
    {
        AttrRouteFacade::register(__DIR__ . '/Fixtures');

        $this->assertNotNull($this->findRoute('users/list', 'GET'));
    }
}

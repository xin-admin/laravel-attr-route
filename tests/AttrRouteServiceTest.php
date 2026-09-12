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

        // UserController 与 AdminController 的路由都被注册
        $this->assertNotNull($this->findRoute('users/list', 'GET'));
        $this->assertNotNull($this->findRoute('admin/dashboard', 'GET'));

        // 没有 RequestAttribute 的控制器不会被注册
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
        // 非 *Controller.php 文件不应被扫描
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
        // 无命名空间的文件无法映射到可加载的类，静默跳过
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

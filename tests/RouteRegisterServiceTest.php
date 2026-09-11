<?php

namespace Xin\AnnoRoute\Tests;

use Illuminate\Support\Facades\Route as RouteFacade;
use Laravel\Sanctum\Http\Middleware\CheckAbilities;
use Xin\AnnoRoute\Middlewares\CheckModelMiddleware;
use Xin\AnnoRoute\RouteRegisterService;
use Xin\AnnoRoute\Tests\Fixtures\AdminController;
use Xin\AnnoRoute\Tests\Fixtures\BareController;
use Xin\AnnoRoute\Tests\Fixtures\PlainController;
use Xin\AnnoRoute\Tests\Fixtures\UserController;

class RouteRegisterServiceTest extends TestCase
{
    /**
     * Fixture 控制器未显式指定 authModel，RequestAttribute 默认 'default'，
     * 因此 CheckModelMiddleware 会以 ':default' 参数注册。
     */
    private const MODEL_GUARD = CheckModelMiddleware::class . ':default';

    public function test_registers_get_route_with_prefix(): void
    {
        RouteRegisterService::register(UserController::class);

        $route = $this->findRoute('users/list', 'GET');

        $this->assertNotNull($route);
        $this->assertSame(UserController::class . '@index', $route->getAction('uses'));
        // authorize: false → 不追加任何鉴权中间件
        $this->assertSame([], $route->middleware());
    }

    public function test_registers_routes_for_all_http_methods(): void
    {
        RouteRegisterService::register(UserController::class);

        $this->assertNotNull($this->findRoute('users/create', 'POST'));
        $this->assertNotNull($this->findRoute('users/{id}', 'PUT'));
        $this->assertNotNull($this->findRoute('users/{id}/status', 'PATCH'));
        $this->assertNotNull($this->findRoute('users/{id}', 'DELETE'));
    }

    public function test_any_route_responds_to_all_common_methods(): void
    {
        RouteRegisterService::register(UserController::class);

        $route = $this->findRoute('users/ping', 'GET');

        $this->assertNotNull($route);
        foreach (['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'] as $method) {
            $this->assertContains($method, $route->methods());
        }
    }

    public function test_string_authorize_builds_abilities_middleware_with_prefix(): void
    {
        RouteRegisterService::register(UserController::class);

        $middleware = $this->findRoute('users/create', 'POST')->middleware();

        $this->assertSame(
            ['auth:sanctum', self::MODEL_GUARD, CheckAbilities::class . ':user.create'],
            $middleware
        );
    }

    public function test_string_authorize_without_abilities_prefix(): void
    {
        RouteRegisterService::register(AdminController::class);

        $middleware = $this->findRoute('admin/dashboard', 'GET')->middleware();

        $this->assertSame(
            ['auth:sanctum', self::MODEL_GUARD, CheckAbilities::class . ':dashboard.view', 'web'],
            $middleware
        );
    }

    public function test_true_authorize_skips_abilities_middleware(): void
    {
        RouteRegisterService::register(AdminController::class);

        $middleware = $this->findRoute('admin/settings', 'POST')->middleware();

        $this->assertSame(['auth:sanctum', self::MODEL_GUARD, 'web'], $middleware);
    }

    public function test_method_middleware_string_is_normalized_and_merged(): void
    {
        RouteRegisterService::register(UserController::class);

        // authorize 默认 true → 无 abilities；方法中间件字符串被规整为数组
        $middleware = $this->findRoute('users/{id}', 'PUT')->middleware();

        $this->assertSame(['auth:sanctum', self::MODEL_GUARD, 'throttle:60'], $middleware);
    }

    public function test_method_middleware_array_is_merged(): void
    {
        RouteRegisterService::register(UserController::class);

        $middleware = $this->findRoute('users/{id}/status', 'PATCH')->middleware();

        $this->assertSame(['auth:sanctum', self::MODEL_GUARD, 'web'], $middleware);
    }

    public function test_where_constraints_are_applied(): void
    {
        RouteRegisterService::register(UserController::class);

        $route = $this->findRoute('users/{id}', 'DELETE');

        $this->assertSame(['id' => '[0-9]+'], $route->wheres);
    }

    public function test_only_route_attribute_methods_are_registered(): void
    {
        RouteRegisterService::register(UserController::class);

        // 8 个带路由注解的方法；notARoute 与无关注解不产生额外路由
        $this->assertCount(8, RouteFacade::getRoutes()->getRoutes());
    }

    public function test_class_without_request_attribute_is_skipped(): void
    {
        RouteRegisterService::register(PlainController::class);

        $this->assertCount(0, RouteFacade::getRoutes()->getRoutes());
    }

    public function test_class_without_any_attribute_is_skipped(): void
    {
        RouteRegisterService::register(BareController::class);

        $this->assertCount(0, RouteFacade::getRoutes()->getRoutes());
    }

    public function test_public_route_is_reachable_over_http(): void
    {
        RouteRegisterService::register(UserController::class);

        $this->get('/users/list')->assertOk()->assertSee('user.list');
        $this->get('/users/ping')->assertOk();
        $this->post('/users/ping')->assertOk();
    }

    public function test_where_constraint_rejects_invalid_parameter_over_http(): void
    {
        RouteRegisterService::register(UserController::class);

        $this->get('/users/42/detail')->assertOk();
        $this->get('/users/abc/detail')->assertNotFound();
    }

    public function test_build_auth_middleware(): void
    {
        $guard = CheckModelMiddleware::class;
        $abilities = CheckAbilities::class;

        // authorize = false → 无鉴权中间件
        $this->assertSame([], RouteRegisterService::buildAuthMiddleware(false, 'default', ''));

        // authorize = true → 仅登录校验，无 abilities
        $this->assertSame(
            ['auth:sanctum', $guard . ':default'],
            RouteRegisterService::buildAuthMiddleware(true, 'default', '')
        );

        // 字符串 authorize + 权限前缀 → 拼接为 prefix.authorize
        $this->assertSame(
            ['auth:sanctum', $guard . ':default', $abilities . ':user.create'],
            RouteRegisterService::buildAuthMiddleware('create', 'default', 'user')
        );

        // 字符串 authorize、空前缀 → 直接使用 authorize
        $this->assertSame(
            ['auth:sanctum', $guard . ':default', $abilities . ':create'],
            RouteRegisterService::buildAuthMiddleware('create', 'default', '')
        );

        // authModel 为 null / 空串 → CheckModelMiddleware 不带参数（运行时回退默认模型配置）
        $this->assertSame(
            ['auth:sanctum', $guard],
            RouteRegisterService::buildAuthMiddleware(true, null, '')
        );
        $this->assertSame(
            ['auth:sanctum', $guard],
            RouteRegisterService::buildAuthMiddleware(true, '', '')
        );

        // authModel 为具体模型类 → 作为参数传递
        $this->assertSame(
            ['auth:sanctum', $guard . ':App\Models\Admin'],
            RouteRegisterService::buildAuthMiddleware(true, 'App\Models\Admin', '')
        );
    }

    public function test_normalize_middleware(): void
    {
        $this->assertSame([], RouteRegisterService::normalizeMiddleware(null));
        $this->assertSame([], RouteRegisterService::normalizeMiddleware(''));
        $this->assertSame([], RouteRegisterService::normalizeMiddleware([]));
        $this->assertSame(['auth'], RouteRegisterService::normalizeMiddleware('auth'));
        $this->assertSame(['a', 'b'], RouteRegisterService::normalizeMiddleware(['a', 'b']));
    }
}

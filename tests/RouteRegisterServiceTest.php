<?php

namespace Xin\AttrRoute\Tests;

use Illuminate\Support\Facades\Route as RouteFacade;
use Laravel\Sanctum\Http\Middleware\CheckAbilities;
use Xin\AttrRoute\Middlewares\CheckModelMiddleware;
use Xin\AttrRoute\RouteRegisterService;
use Xin\AttrRoute\Tests\Fixtures\AdminController;
use Xin\AttrRoute\Tests\Fixtures\BareController;
use Xin\AttrRoute\Tests\Fixtures\PlainController;
use Xin\AttrRoute\Tests\Fixtures\UserController;

class RouteRegisterServiceTest extends TestCase
{
    /**
     * UserController does not specify authModel, so RequestAttribute defaults to '',
     * and an empty authModel falls back to 'default' — registered with the ':default' parameter.
     */
    private const MODEL_GUARD = CheckModelMiddleware::class . ':default';

    public function test_registers_get_route_with_prefix(): void
    {
        RouteRegisterService::register(UserController::class);

        $route = $this->findRoute('users/list', 'GET');

        $this->assertNotNull($route);
        $this->assertSame(UserController::class . '@index', $route->getAction('uses'));
        // authorize: false → no auth middleware is appended
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

        // AdminController specifies authModel: 'admin'
        $middleware = $this->findRoute('admin/dashboard', 'GET')->middleware();

        $this->assertSame(
            [
                'auth:sanctum',
                CheckModelMiddleware::class . ':admin',
                CheckAbilities::class . ':dashboard.view',
                'web',
            ],
            $middleware
        );
    }

    public function test_true_authorize_skips_abilities_middleware(): void
    {
        RouteRegisterService::register(AdminController::class);

        $middleware = $this->findRoute('admin/settings', 'POST')->middleware();

        $this->assertSame(
            ['auth:sanctum', CheckModelMiddleware::class . ':admin', 'web'],
            $middleware
        );
    }

    public function test_method_middleware_string_is_normalized_and_merged(): void
    {
        RouteRegisterService::register(UserController::class);

        // authorize defaults to true → no abilities; the method middleware string is normalized to an array
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

        // 8 methods carry route attributes; notARoute and CustomAttribute produce no extra routes
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

        // authorize = false → no auth middleware
        $this->assertSame([], RouteRegisterService::buildAuthMiddleware(false, 'admin', ''));

        // authorize = true → authentication only, no abilities
        $this->assertSame(
            ['auth:sanctum', $guard . ':admin'],
            RouteRegisterService::buildAuthMiddleware(true, 'admin', '')
        );

        // String authorize + abilities prefix → concatenated as prefix.authorize
        $this->assertSame(
            ['auth:sanctum', $guard . ':admin', $abilities . ':user.create'],
            RouteRegisterService::buildAuthMiddleware('create', 'admin', 'user')
        );

        // String authorize with an empty prefix → use authorize as-is
        $this->assertSame(
            ['auth:sanctum', $guard . ':admin', $abilities . ':create'],
            RouteRegisterService::buildAuthMiddleware('create', 'admin', '')
        );

        // authModel null / empty → falls back to ':default' (resolves the models.default config at runtime)
        $this->assertSame(
            ['auth:sanctum', $guard . ':default'],
            RouteRegisterService::buildAuthMiddleware(true, null, '')
        );
        $this->assertSame(
            ['auth:sanctum', $guard . ':default'],
            RouteRegisterService::buildAuthMiddleware(true, '', '')
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

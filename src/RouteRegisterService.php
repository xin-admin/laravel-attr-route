<?php

namespace Xin\AttrRoute;

use Illuminate\Support\Facades\Route;
use Laravel\Sanctum\Http\Middleware\CheckAbilities;
use ReflectionClass;
use ReflectionException;
use Xin\AttrRoute\Attribute\AnyRoute;
use Xin\AttrRoute\Attribute\BaseAttribute;
use Xin\AttrRoute\Attribute\DeleteRoute;
use Xin\AttrRoute\Attribute\GetRoute;
use Xin\AttrRoute\Attribute\PatchRoute;
use Xin\AttrRoute\Attribute\PostRoute;
use Xin\AttrRoute\Attribute\PutRoute;
use Xin\AttrRoute\Attribute\RequestAttribute;
use Xin\AttrRoute\Middlewares\CheckModelMiddleware;

class RouteRegisterService
{
    /**
     * 默认的注解 => HTTP 方法映射表
     *
     * @var array<class-string<BaseAttribute>, string>
     */
    private static array $mappings = [
        GetRoute::class => 'get',
        PostRoute::class => 'post',
        PutRoute::class => 'put',
        PatchRoute::class => 'patch',
        DeleteRoute::class => 'delete',
        AnyRoute::class => 'any',
    ];

    /**
     * register 注册路由
     *
     * @param class-string $className
     * @throws ReflectionException
     */
    public static function register(string $className): void
    {
        $classRef = new ReflectionClass($className);

        $classAttr = collect($classRef->getAttributes());
        if ($classAttr->isEmpty()) {
            return;
        }

        $classAttrName = $classAttr->map->getName();
        if (! $classAttrName->contains(RequestAttribute::class)) {
            return;
        }

        $requestMapping = $classAttr->first(fn ($item) => $item->getName() === RequestAttribute::class);

        if($requestMapping == null) {
            return;
        }
        $routeInstance = $requestMapping->newInstance();
        // 默认参数
        $routePrefix = $routeInstance->routePrefix ?? '';
        $authModel = $routeInstance->authModel ?? 'default';
        $abilitiesPrefix = $routeInstance->abilitiesPrefix ?? '';
        $middleware = self::normalizeMiddleware($routeInstance->middleware);

        $mappings = self::$mappings;

        foreach ($classRef->getMethods() as $method) {
            // 方法注解
            $attributes = $method->getAttributes();
            if (empty($attributes)) {
                continue;
            }
            $methodName = $method->getName();

            foreach ($attributes as $attribute) {
                $attributeName = $attribute->getName();
                if (array_key_exists($attributeName, $mappings)) {
                    $instance = $attribute->newInstance();
                    self::registerRoute(
                        $instance,
                        $methodName,
                        $className,
                        $mappings[$attributeName],
                        $authModel,
                        $middleware,
                        $routePrefix,
                        $abilitiesPrefix,
                    );
                }
            }
        }
    }

    /**
     * 注册路由
     *
     * @param BaseAttribute $instance 路由注解实例
     * @param string $method 控制器方法名
     * @param class-string $className 控制器类名
     * @param string $httpMethod HTTP 方法（小写）
     * @param string $authModel 用户模型
     * @param array $middleware 控制器级中间件
     * @param string $routePrefix 路由前缀
     * @param string $abilitiesPrefix 权限前缀
     * @return void
     */
    private static function registerRoute(
        BaseAttribute $instance,
        string $method,
        string $className,
        string $httpMethod,
        string $authModel,
        array $middleware = [],
        string $routePrefix = '',
        string $abilitiesPrefix = ''
    ): void
    {
        $authMiddleware = self::buildAuthMiddleware($instance->authorize, $authModel, $abilitiesPrefix);

        $middleware = array_merge($authMiddleware, self::normalizeMiddleware($instance->middleware), $middleware);
        $route = Route::{$httpMethod}(
            $routePrefix . $instance->route,
            [$className, $method]
        )->middleware(array_unique($middleware));

        if (!empty($instance->where)) {
            $route->where($instance->where);
        }
    }

    /**
     * 根据 authorize 注解参数构建鉴权中间件
     *
     * @param string|bool $authorize 权限字段
     * @param string|null $authModel 用户模型
     * @param string $abilitiesPrefix 权限前缀
     * @return array
     */
    public static function buildAuthMiddleware(string|bool $authorize, ?string $authModel, string $abilitiesPrefix): array
    {
        if ($authorize === false) {
            return [];
        }

        $authMiddleware = ['auth:sanctum'];

        if (! empty($authModel)) {
            $authMiddleware[] = CheckModelMiddleware::class . ':' . $authModel;
        } else {
            $authMiddleware[] = CheckModelMiddleware::class . ':default';
        }

        $abilitiesMiddleware = CheckAbilities::class;
        if (is_string($authorize) && !empty($abilitiesPrefix)) {
            $authMiddleware[] = $abilitiesMiddleware . ':' . $abilitiesPrefix . '.' . $authorize;
        } elseif ($authorize !== true) {
            $authMiddleware[] = $abilitiesMiddleware . ':' . $authorize;
        }

        return $authMiddleware;
    }

    /**
     * 规整中间件参数为数组
     *
     * @param string|array|null $middleware
     * @return string[]
     */
    public static function normalizeMiddleware(string|array|null $middleware): array
    {
        if (empty($middleware)) {
            return [];
        }
        if (is_array($middleware)) {
            return $middleware;
        }
        return [$middleware];
    }
}

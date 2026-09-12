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
     * Default attribute => HTTP method mapping table
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
     * Register routes
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
        // Default parameters
        $routePrefix = $routeInstance->routePrefix ?? '';
        $authModel = $routeInstance->authModel ?? 'default';
        $abilitiesPrefix = $routeInstance->abilitiesPrefix ?? '';
        $middleware = self::normalizeMiddleware($routeInstance->middleware);

        $mappings = self::$mappings;

        foreach ($classRef->getMethods() as $method) {
            // Method attributes
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
     * Register a route
     *
     * @param BaseAttribute $instance Route attribute instance
     * @param string $method Controller method name
     * @param class-string $className Controller class name
     * @param string $httpMethod HTTP method (lowercase)
     * @param string $authModel User model guard alias
     * @param array $middleware Controller-level middleware
     * @param string $routePrefix Route prefix
     * @param string $abilitiesPrefix Abilities prefix
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
     * Build authorization middleware from the authorize attribute parameter
     *
     * @param string|bool $authorize Ability name
     * @param string|null $authModel User model guard alias
     * @param string $abilitiesPrefix Abilities prefix
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
     * Normalize the middleware parameter into an array
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

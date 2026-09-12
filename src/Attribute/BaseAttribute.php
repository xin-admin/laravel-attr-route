<?php

namespace Xin\AttrRoute\Attribute;

use Attribute;
use Illuminate\Http\JsonResponse;

#[Attribute]
abstract class BaseAttribute
{
    /**
     * Routing address, use this attribute to specify the routing address. The complete routing address is composed
     * of the routePrefix of RequestAttribute and the route concatenated, for example: If the routePrefix of RequestAttribute
     * is /admin/user and the route of Mapping is /create, then the complete routing address is /admin/user/create
     *
     * @var string
     */
    public string $route = '';

    /**
     * Route middleware, this attribute is used to specify the route middleware, multiple middleware are passed in
     * as an array, for example: ['auth', 'admin']
     *
     * @var string|array
     */
    public string | array $middleware = '';

    /**
     * HTTP request method
     *
     * @var string
     */
    public string $httpMethod = 'POST';

    /**
     * Permission string, this attribute is used to specify the permission string, used for permission authentication,
     * the complete permission string is composed of the abilitiesPrefix of RequestAttribute and the authorize concatenated,
     * for example: If the abilitiesPrefix of RequestAttribute is admin and the authorize of Mapping is user, then the complete
     * permission string is admin.user
     *
     * @var string | bool
     */
    public string | bool $authorize = true;

    /**
     * Route parameter constraints, this attribute is used to specify regex constraints for route parameters,
     * for example: ['id' => '[0-9]+']
     *
     * @var array
     */
    public array $where = [];
}

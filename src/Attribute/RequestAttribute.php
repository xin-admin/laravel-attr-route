<?php

namespace Xin\AttrRoute\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class RequestAttribute
{
    /**
     * @param string $routePrefix 路由前缀
     * @param string $abilitiesPrefix 权限前缀
     * @param string | array $middleware 控制器中间件
     * @param string | null $authModel 约束用户模型
     */
    public function __construct(
        public string $routePrefix = '',
        public string $abilitiesPrefix = '',
        public string | array $middleware = '',
        public ?string $authModel = '',
    )
    {
    }
}

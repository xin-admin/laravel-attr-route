<?php

namespace Xin\AttrRoute\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class RequestAttribute
{
    /**
     * @param string $routePrefix Route prefix
     * @param string $abilitiesPrefix Abilities prefix
     * @param string | array $middleware Controller middleware
     * @param string | null $authModel User model guard alias
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

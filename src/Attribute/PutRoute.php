<?php

namespace Xin\AttrRoute\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class PutRoute extends BaseAttribute
{
    /** @var string HTTP request method */
    public string $httpMethod = 'PUT';

    /**
     * @param string $route Route path
     * @param string|bool $authorize Ability name, or false to skip authorization
     * @param string|array $middleware Middleware
     * @param array $where Route parameter constraints
     */
    public function __construct(
        public string         $route = '',
        public string|bool    $authorize = true,
        public string | array $middleware = '',
        public array          $where = [],
    )
    {
    }
}

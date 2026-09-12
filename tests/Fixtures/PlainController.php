<?php

namespace Xin\AttrRoute\Tests\Fixtures;

use Xin\AttrRoute\Attribute\GetRoute;

/**
 * 类上有注解但不是 RequestAttribute 的控制器，不应注册任何路由。
 */
#[CustomAttribute]
class PlainController
{
    #[GetRoute('/plain', authorize: false)]
    public function index(): string
    {
        return 'plain';
    }
}

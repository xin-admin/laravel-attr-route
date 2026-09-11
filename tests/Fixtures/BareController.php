<?php

namespace Xin\AttrRoute\Tests\Fixtures;

/**
 * 完全没有任何注解的控制器，不应注册任何路由。
 */
class BareController
{
    public function index(): string
    {
        return 'bare';
    }
}

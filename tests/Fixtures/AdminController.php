<?php

namespace Xin\AttrRoute\Tests\Fixtures;

use Xin\AttrRoute\Attribute\GetRoute;
use Xin\AttrRoute\Attribute\PostRoute;
use Xin\AttrRoute\Attribute\RequestAttribute;

/**
 * 带控制器级中间件、无权限前缀、显式指定 authModel 的控制器，
 * 用于验证中间件合并、无前缀的 abilities 拼接与 authModel 透传。
 */
#[RequestAttribute(routePrefix: '/admin', middleware: ['web'], authModel: 'admin')]
class AdminController
{
    #[GetRoute('/dashboard', authorize: 'dashboard.view')]
    public function dashboard(): string
    {
        return 'admin.dashboard';
    }

    #[PostRoute('/settings', authorize: true)]
    public function settings(): string
    {
        return 'admin.settings';
    }
}

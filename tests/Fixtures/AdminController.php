<?php

namespace Xin\AttrRoute\Tests\Fixtures;

use Xin\AttrRoute\Attribute\GetRoute;
use Xin\AttrRoute\Attribute\PostRoute;
use Xin\AttrRoute\Attribute\RequestAttribute;

/**
 * 带控制器级中间件、无权限前缀的控制器，
 * 用于验证中间件合并与无前缀的 abilities 拼接。
 */
#[RequestAttribute(routePrefix: '/admin', middleware: ['web'])]
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

<?php

namespace Xin\AnnoRoute\Tests\Fixtures;

use Xin\AnnoRoute\Attribute\GetRoute;
use Xin\AnnoRoute\Attribute\PostRoute;
use Xin\AnnoRoute\Attribute\RequestAttribute;

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

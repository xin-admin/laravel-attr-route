<?php

namespace Xin\AttrRoute\Tests\Fixtures;

use Xin\AttrRoute\Attribute\GetRoute;
use Xin\AttrRoute\Attribute\PostRoute;
use Xin\AttrRoute\Attribute\RequestAttribute;

/**
 * A controller with controller-level middleware, no abilities prefix, and an explicit authModel,
 * used to verify middleware merging, prefix-less ability building, and authModel passthrough.
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

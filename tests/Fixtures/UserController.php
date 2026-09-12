<?php

namespace Xin\AttrRoute\Tests\Fixtures;

use Xin\AttrRoute\Attribute\AnyRoute;
use Xin\AttrRoute\Attribute\DeleteRoute;
use Xin\AttrRoute\Attribute\GetRoute;
use Xin\AttrRoute\Attribute\PatchRoute;
use Xin\AttrRoute\Attribute\PostRoute;
use Xin\AttrRoute\Attribute\PutRoute;
use Xin\AttrRoute\Attribute\RequestAttribute;

/**
 * 带权限前缀的控制器，覆盖全部六种 HTTP 方法注解。
 */
#[RequestAttribute(routePrefix: '/users', abilitiesPrefix: 'user')]
class UserController
{
    #[GetRoute('/list', authorize: false)]
    public function index(): string
    {
        return 'user.list';
    }

    #[PostRoute('/create', authorize: 'create')]
    public function store(): string
    {
        return 'user.create';
    }

    #[PutRoute('/{id}', middleware: 'throttle:60')]
    public function update(int $id): string
    {
        return 'user.update';
    }

    #[PatchRoute('/{id}/status', middleware: ['web'])]
    public function status(int $id): string
    {
        return 'user.status';
    }

    #[DeleteRoute('/{id}', authorize: false, where: ['id' => '[0-9]+'])]
    public function destroy(int $id): string
    {
        return 'user.destroy';
    }

    #[GetRoute('/{id}/detail', authorize: false, where: ['id' => '[0-9]+'])]
    public function detail(int $id): string
    {
        return 'user.detail';
    }

    #[AnyRoute('/ping', authorize: false)]
    public function ping(): string
    {
        return 'pong';
    }

    #[CustomAttribute]
    #[GetRoute('/flagged', authorize: false)]
    public function flagged(): string
    {
        return 'user.flagged';
    }

    public function notARoute(): string
    {
        return 'plain';
    }
}

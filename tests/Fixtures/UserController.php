<?php

namespace Xin\AnnoRoute\Tests\Fixtures;

use Xin\AnnoRoute\Attribute\AnyRoute;
use Xin\AnnoRoute\Attribute\DeleteRoute;
use Xin\AnnoRoute\Attribute\GetRoute;
use Xin\AnnoRoute\Attribute\PatchRoute;
use Xin\AnnoRoute\Attribute\PostRoute;
use Xin\AnnoRoute\Attribute\PutRoute;
use Xin\AnnoRoute\Attribute\RequestAttribute;

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

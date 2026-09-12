<?php

namespace Xin\AttrRoute\Tests;

use Illuminate\Auth\Access\AuthorizationException;
use Xin\AttrRoute\Exceptions\MissingModelException;

class MissingModelExceptionTest extends TestCase
{
    public function test_extends_authorization_exception(): void
    {
        $this->assertInstanceOf(AuthorizationException::class, new MissingModelException('User'));
    }

    public function test_model_accessor_returns_model(): void
    {
        $exception = new MissingModelException('App\Models\User');

        $this->assertSame('App\Models\User', $exception->model());
    }

    public function test_default_message(): void
    {
        $this->assertSame('Invalid model provided.', (new MissingModelException('User'))->getMessage());
    }

    public function test_custom_message(): void
    {
        $this->assertSame('自定义错误', (new MissingModelException('User', '自定义错误'))->getMessage());
    }
}

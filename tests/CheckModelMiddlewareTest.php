<?php

namespace Xin\AttrRoute\Tests;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;
use Symfony\Component\HttpFoundation\Response;
use Xin\AttrRoute\Exceptions\MissingModelException;
use Xin\AttrRoute\Middlewares\CheckModelMiddleware;
use Xin\AttrRoute\Tests\Fixtures\Models\AdminUser;
use Xin\AttrRoute\Tests\Fixtures\Models\SystemUser;

class CheckModelMiddlewareTest extends TestCase
{
    public function test_throws_when_no_authenticated_user(): void
    {
        $this->expectException(AuthorizationException::class);
        $this->expectExceptionMessage('Invalid token provided.');

        (new CheckModelMiddleware())->handle($this->makeRequest(null), fn () => new Response(), AdminUser::class);
    }

    public function test_throws_when_access_token_is_null(): void
    {
        $user = new class {
            public function currentAccessToken()
            {
                return null;
            }
        };

        $this->expectException(AuthorizationException::class);
        $this->expectExceptionMessage('Invalid token provided.');

        (new CheckModelMiddleware())->handle($this->makeRequest($user), fn () => new Response(), AdminUser::class);
    }

    public function test_throws_when_access_token_is_not_personal_access_token(): void
    {
        $user = new class {
            public function currentAccessToken(): object
            {
                return new \stdClass();
            }
        };

        $this->expectException(AuthorizationException::class);
        $this->expectExceptionMessage('Invalid token provided.');

        (new CheckModelMiddleware())->handle($this->makeRequest($user), fn () => new Response(), AdminUser::class);
    }

    public function test_throws_when_tokenable_model_mismatches(): void
    {
        $token = new PersonalAccessToken();
        $token->setRelation('tokenable', new SystemUser());

        try {
            (new CheckModelMiddleware())->handle(
                $this->makeRequest($this->userWithToken($token)),
                fn () => new Response(),
                AdminUser::class
            );
            $this->fail('令牌模型不匹配时应抛出 MissingModelException');
        } catch (MissingModelException $e) {
            $this->assertSame('Invalid model provided.', $e->getMessage());
            $this->assertSame((string) (new SystemUser()), $e->model());
        }
    }

    public function test_passes_when_tokenable_matches_model(): void
    {
        $token = new PersonalAccessToken();
        $token->setRelation('tokenable', new AdminUser());

        $called = false;
        $response = (new CheckModelMiddleware())->handle(
            $this->makeRequest($this->userWithToken($token)),
            function () use (&$called) {
                $called = true;
                return new Response('OK');
            },
            AdminUser::class
        );

        $this->assertTrue($called);
        $this->assertSame('OK', $response->getContent());
    }

    public function test_falls_back_to_config_default_model_when_model_class_is_null(): void
    {
        config()->set('attr-route.models.default', AdminUser::class);

        $token = new PersonalAccessToken();
        $token->setRelation('tokenable', new AdminUser());

        $called = false;
        (new CheckModelMiddleware())->handle(
            $this->makeRequest($this->userWithToken($token)),
            function () use (&$called) {
                $called = true;
                return new Response('OK');
            },
            null
        );

        $this->assertTrue($called);
    }

    public function test_throws_when_model_class_and_config_default_are_both_empty(): void
    {
        // 包默认配置 models.default = ''，instanceof 空类名恒为 false
        $token = new PersonalAccessToken();
        $token->setRelation('tokenable', new AdminUser());

        $this->expectException(MissingModelException::class);

        (new CheckModelMiddleware())->handle(
            $this->makeRequest($this->userWithToken($token)),
            fn () => new Response(),
            null
        );
    }

    private function makeRequest(?object $user): Request
    {
        $request = Request::create('/test', 'GET');
        $request->setUserResolver(fn () => $user);

        return $request;
    }

    private function userWithToken(PersonalAccessToken $token): object
    {
        return new class($token) {
            public function __construct(private PersonalAccessToken $token)
            {
            }

            public function currentAccessToken(): PersonalAccessToken
            {
                return $this->token;
            }
        };
    }
}

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
    protected function getEnvironmentSetUp($app): void
    {
        parent::getEnvironmentSetUp($app);

        $app['config']->set('attr-route.models.admin', AdminUser::class);
    }

    public function test_passes_through_when_alias_has_no_configured_model(): void
    {
        // 包默认配置 models.default = ''，未配置模型时不校验直接放行
        $called = false;
        $response = (new CheckModelMiddleware())->handle(
            $this->makeRequest(null),
            function () use (&$called) {
                $called = true;
                return new Response('OK');
            },
            'default'
        );

        $this->assertTrue($called);
        $this->assertSame('OK', $response->getContent());
    }

    public function test_passes_through_when_alias_is_not_configured(): void
    {
        $called = false;
        (new CheckModelMiddleware())->handle(
            $this->makeRequest(null),
            function () use (&$called) {
                $called = true;
                return new Response();
            },
            'not-exists'
        );

        $this->assertTrue($called);
    }

    public function test_null_alias_falls_back_to_default(): void
    {
        // null 别名回退 'default'，默认未配置模型 → 放行
        $called = false;
        (new CheckModelMiddleware())->handle(
            $this->makeRequest(null),
            function () use (&$called) {
                $called = true;
                return new Response();
            },
            null
        );

        $this->assertTrue($called);
    }

    public function test_throws_when_no_authenticated_user(): void
    {
        $this->expectException(AuthorizationException::class);
        $this->expectExceptionMessage('Invalid token provided.');

        (new CheckModelMiddleware())->handle($this->makeRequest(null), fn () => new Response(), 'admin');
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

        (new CheckModelMiddleware())->handle($this->makeRequest($user), fn () => new Response(), 'admin');
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

        (new CheckModelMiddleware())->handle($this->makeRequest($user), fn () => new Response(), 'admin');
    }

    public function test_throws_when_tokenable_model_mismatches(): void
    {
        $tokenable = new SystemUser();
        $token = new PersonalAccessToken();
        $token->setRelation('tokenable', $tokenable);

        try {
            (new CheckModelMiddleware())->handle(
                $this->makeRequest($this->userWithToken($token)),
                fn () => new Response(),
                'admin'
            );
            $this->fail('令牌模型不匹配时应抛出 MissingModelException');
        } catch (MissingModelException $e) {
            $this->assertSame('Invalid model provided.', $e->getMessage());
            $this->assertSame((string) $tokenable, $e->model());
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
            'admin'
        );

        $this->assertTrue($called);
        $this->assertSame('OK', $response->getContent());
    }

    public function test_uses_default_model_config_when_alias_is_null(): void
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

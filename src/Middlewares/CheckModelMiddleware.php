<?php

namespace Xin\AttrRoute\Middlewares;

use Closure;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;
use Symfony\Component\HttpFoundation\Response;
use Xin\AttrRoute\Exceptions\MissingModelException;

class CheckModelMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param Closure(Request): (Response) $next
     */
    public function handle(Request $request, Closure $next, ?string $modelClassAlias): Response
    {
        if (empty($modelClassAlias)) {
            $modelClassAlias = 'default';
        }

        $modelClass = config('attr-route.models.' . $modelClassAlias);

        if (empty($modelClass)) {
            return $next($request);
        }

        $token = $request->user()?->currentAccessToken();

        if (! $token instanceof PersonalAccessToken) {
            throw new AuthorizationException('Invalid token provided.');
        }
        $tokenable = $token->tokenable;

        if (! $tokenable instanceof $modelClass) {
            throw new MissingModelException($tokenable);
        }

        return $next($request);
    }
}

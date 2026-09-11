<?php

namespace Xin\AnnoRoute\Middlewares;

use Closure;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;
use Symfony\Component\HttpFoundation\Response;
use Xin\AnnoRoute\Exceptions\MissingModelException;

class AuthGuardMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param Closure(Request): (Response) $next
     */
    public function handle(Request $request, Closure $next, string $modelClass): Response
    {

        $token = $request->user()?->currentAccessToken();

        if (! $token instanceof PersonalAccessToken) {
            throw new MissingModelException('', 'Invalid token provided.');
        }
        $tokenable = $token->tokenable;

        if (! $tokenable instanceof $modelClass) {
            throw new MissingModelException($tokenable);
        }

        return $next($request);
    }
}

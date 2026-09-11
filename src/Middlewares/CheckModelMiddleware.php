<?php

namespace Xin\AnnoRoute\Middlewares;

use Closure;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;
use Symfony\Component\HttpFoundation\Response;
use Xin\AnnoRoute\Exceptions\MissingModelException;

class CheckModelMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param Closure(Request): (Response) $next
     */
    public function handle(Request $request, Closure $next, ?string $modelClass): Response
    {
        if (empty($modelClass)) {
            $modelClass = config('attr-route.models.default');
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

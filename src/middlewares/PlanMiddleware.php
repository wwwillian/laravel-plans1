<?php

namespace Wwwillian\LaravelPlans\Middlewares;

use Closure;
use Illuminate\Support\Facades\Auth;
use Wwwillian\LaravelPlans\Exceptions\UnauthorizedException;

class PlanMiddleware
{
    public function handle($request, Closure $next, $plan)
    {
        if (Auth::guest()) {
            throw UnauthorizedException::notLoggedIn();
        }

        if (! app('Company')->hasPlan($plan)) {
            throw UnauthorizedException::forPlan($plan);
        }

        return $next($request);
    }
}

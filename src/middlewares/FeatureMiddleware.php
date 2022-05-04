<?php

namespace Wwwillian\LaravelPlans\Middlewares;

use Closure;
use Wwwillian\LaravelPlans\Exceptions\UnauthorizedException;

class FeatureMiddleware
{
    public function handle($request, Closure $next, $feature)
    {
        if (app('auth')->guest()) {
            throw UnauthorizedException::notLoggedIn();
        }

        $features = is_array($feature)
            ? $feature
            : explode('|', $feature);

        foreach ($features as $feature) {
            if (app('Company')->hasAllFeatures($feature)) {
                return $next($request);
            }
        }

        throw UnauthorizedException::forFeatures($features);
    }
}

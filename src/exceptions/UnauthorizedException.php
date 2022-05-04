<?php

namespace Wwwillian\LaravelPlans\Exceptions;

use Symfony\Component\HttpKernel\Exception\HttpException;

class UnauthorizedException extends HttpException
{
    private $requiredPlan = [];

    private $requiredFeatures = [];

    public static function forPlan(string $plan): self
    {
        $message = trans('plans.plan_required_exception');

        $exception = new static(403, $message, null, []);
        $exception->requiredPlan = $plan;

        return $exception;
    }

    public static function forFeatures(array $features): self
    {
        $message = trans('plans.features_required_exception');

        $exception = new static(403, $message, null, []);
        $exception->requiredFeatures = $features;

        return $exception;
    }

    public static function notLoggedIn(): self
    {
        return new static(403, trans('plans.user_not_logged'), null, []);
    }

    public function getRequiredPlan(): array
    {
        return $this->requiredPlan;
    }

    public function getRequiredFeatures(): array
    {
        return $this->requiredFeatures;
    }
}

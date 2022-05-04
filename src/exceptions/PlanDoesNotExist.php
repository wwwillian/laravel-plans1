<?php

namespace Wwwillian\LaravelPlans\Exceptions;

use InvalidArgumentException;

class PlanDoesNotExist extends InvalidArgumentException
{
    public static function named(string $planName)
    {
        return new static("There is no plan named `{$planName}`.");
    }

    public static function withId(int $planId)
    {
        return new static("There is no plan with id `{$planId}`.");
    }
}

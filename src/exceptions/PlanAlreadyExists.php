<?php

namespace MsAlvexx\LaravelPlans\Exceptions;

use InvalidArgumentException;

class PlanAlreadyExists extends InvalidArgumentException
{
    public static function create(string $planName)
    {
        return new static("A role `{$planName}` already exists.");
    }
}

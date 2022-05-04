<?php

namespace Wwwillian\LaravelPlans\Exceptions;

use InvalidArgumentException;

class FeatureAlreadyExists extends InvalidArgumentException
{
    public static function create(string $featureName)
    {
        return new static("A `{$featureName}` feature already exists.");
    }
}

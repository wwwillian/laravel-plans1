<?php

namespace Wwwillian\LaravelPlans\Exceptions;

use InvalidArgumentException;

class FeatureDoesNotExist extends InvalidArgumentException
{
    public static function create(string $featureName)
    {
        return new static("There is no feature named `{$featureName}`.");
    }

    public static function withId(int $featureId)
    {
        return new static("There is no [feature] with id `{$featureId}`.");
    }
}

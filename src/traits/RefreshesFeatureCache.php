<?php

namespace MsAlvexx\LaravelPlans\Traits;

use MsAlvexx\LaravelPlans\Providers\FeatureRegistrar;

trait RefreshesFeatureCache
{
    public static function bootRefreshesFeatureCache()
    {
        static::saved(function () {
            app(FeatureRegistrar::class)->forgetCachedFeatures();
        });

        static::deleted(function () {
            app(FeatureRegistrar::class)->forgetCachedFeatures();
        });
    }
}

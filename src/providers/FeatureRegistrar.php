<?php

namespace MsAlvexx\LaravelPlans\Providers;

use Illuminate\Cache\CacheManager;
use Illuminate\Support\Collection;
use MsAlvexx\LaravelPlans\Contracts\Plan;
use Illuminate\Contracts\Auth\Access\Gate;
use MsAlvexx\LaravelPlans\Contracts\Feature;
use Illuminate\Contracts\Auth\Access\Authorizable;
use MsAlvexx\LaravelPlans\Exceptions\FeatureDoesNotExist;

class FeatureRegistrar
{
    /** @var \Illuminate\Contracts\Auth\Access\Gate */
    protected $gate;

    /** @var \Illuminate\Contracts\Cache\Repository */
    protected $cache;

    /** @var \Illuminate\Cache\CacheManager */
    protected $cacheManager;

    /** @var string */
    protected $featureClass;

    /** @var string */
    protected $planClass;

    /** @var \Illuminate\Support\Collection */
    protected $features;

    /** @var DateInterval|int */
    public static $cacheExpirationTime;

    /** @var string */
    public static $cacheKey;

    /** @var string */
    public static $cacheModelKey;

    /**
     * FeatureRegistrar constructor.
     *
     * @param \Illuminate\Cache\CacheManager $cacheManager
     */
    public function __construct(Gate $gate, CacheManager $cacheManager)
    {
        $this->gate = $gate;
        $this->featureClass = config('plans.models.feature');
        $this->planClass = config('plans.models.plan');

        $this->cacheManager = $cacheManager;
        $this->initializeCache();
    }

    protected function initializeCache()
    {
        self::$cacheExpirationTime = config('plans.cache.expiration_time', config('plans.cache_expiration_time'));

        if (app()->version() <= '5.5') {
            if (self::$cacheExpirationTime instanceof \DateInterval) {
                $interval = self::$cacheExpirationTime;
                self::$cacheExpirationTime = $interval->m * 30 * 60 * 24 + $interval->d * 60 * 24 + $interval->h * 60 + $interval->i;
            }
        }

        self::$cacheKey = config('plans.cache.key');
        self::$cacheModelKey = config('plans.cache.model_key');

        $this->cache = $this->getCacheStoreFromConfig();
    }

    protected function getCacheStoreFromConfig(): \Illuminate\Contracts\Cache\Repository
    {
        // the 'default' fallback here is from the permission.php config file, where 'default' means to use config(cache.default)
        $cacheDriver = config('plans.cache.store', 'default');

        // when 'default' is specified, no action is required since we already have the default instance
        if ($cacheDriver === 'default') {
            return $this->cacheManager->store();
        }

        // if an undefined cache store is specified, fallback to 'array' which is Laravel's closest equiv to 'none'
        if (! \array_key_exists($cacheDriver, config('cache.stores'))) {
            $cacheDriver = 'array';
        }

        return $this->cacheManager->store($cacheDriver);
    }

    /**
     * Register the feature check method on the gate.
     *
     * @return bool
     */
    public function registerFeatures(): bool
    {
        $this->gate->before(function (Authorizable $user, string $ability) {
            try {
                if (method_exists($user, 'hasFeature')) {
                    return $user->hasFeature($ability) ?: null;
                }
            } catch (FeatureDoesNotExist $e) {
            }
        });

        return true;
    }

    /**
     * Flush the cache.
     */
    public function forgetCachedFeatures()
    {
        $this->features = null;
        $this->cache->forget(self::$cacheKey);
    }

    /**
     * Get the features based on the passed params.
     *
     * @param array $params
     *
     * @return \Illuminate\Support\Collection
     */
    public function getFeatures(array $params = []): Collection
    {
        if ($this->features === null) {
            $this->features = $this->cache->remember(self::$cacheKey, self::$cacheExpirationTime, function () {
                return $this->getFeatureClass()
                    ->with('plans')
                    ->get();
            });
        }

        $features = clone $this->features;

        foreach ($params as $attr => $value) {
            $features = $features->where($attr, $value);
        }

        return $features;
    }

    /**
     * Get an instance of the feature class.
     *
     * @return \MsAlvexx\LaravelPlans\Contracts\Feature
     */
    public function getFeatureClass(): Feature
    {
        return app($this->featureClass);
    }

    /**
     * Get an instance of the plan class.
     *
     * @return \MsAlvexx\LaravelPlans\Contracts\Plan
     */
    public function getPlanClass(): Plan
    {
        return app($this->planClass);
    }

    /**
     * Get the instance of the Cache Store.
     *
     * @return \Illuminate\Contracts\Cache\Store
     */
    public function getCacheStore(): \Illuminate\Contracts\Cache\Store
    {
        return $this->cache->getStore();
    }
}

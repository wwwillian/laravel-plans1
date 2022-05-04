<?php

namespace Wwwillian\LaravelPlans\Traits;

use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Builder;
use Wwwillian\LaravelPlans\Providers\FeatureRegistrar;
use Wwwillian\LaravelPlans\Contracts\Feature;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Wwwillian\LaravelPlans\Exceptions\FeatureDoesNotExist;

trait HasFeatures
{
    private $featureClass;

    public static function bootHasFeatures()
    {
        static::deleting(function ($model) {
            if (method_exists($model, 'isForceDeleting') && ! $model->isForceDeleting()) {
                return;
            }

            $model->features()->detach();
        });
    }

    public function getFeatureClass()
    {
        if (! isset($this->featureClass)) {
            $this->featureClass = app(FeatureRegistrar::class)->getFeatureClass();
        }

        return $this->featureClass;
    }

    /**
     * Determine if the model may perform the given feature.
     *
     * @param string|int|\Wwwillian\LaravelPlans\Contracts\Feature $feature
     *
     * @return bool
     * @throws FeatureDoesNotExist
     */
    public function hasFeature($feature): bool
    {
        $featureClass = $this->getFeatureClass();

        if (is_string($feature)) {
            $feature = $featureClass->findByName($feature);
        }

        if (is_int($feature)) {
            $feature = $featureClass->findById($feature);
        }

        if (! $feature instanceof Feature) {
            throw new FeatureDoesNotExist;
        }

        return $this->hasDirectFeature($feature) || $this->hasFeatureViaPlan($feature);
    }

    /**
     * An alias to hasFeature(), but avoids throwing an exception.
     *
     * @param string|int|\Wwwillian\LaravelPlans\Contracts\Feature $feature
     *
     * @return bool
     */
    public function checkFeature($feature): bool
    {
        try {
            return $this->hasFeature($feature);
        } catch (FeatureDoesNotExist $e) {
            return false;
        }
    }

    /**
     * Determine if the model has any of the given features.
     *
     * @param array ...$features
     *
     * @return bool
     * @throws \Exception
     */
    public function hasAnyFeature(...$features): bool
    {
        if (is_array($features[0])) {
            $features = $features[0];
        }

        foreach ($features as $feature) {
            if ($this->checkFeature($feature)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Determine if the model has all of the given features.
     *
     * @param array ...$features
     *
     * @return bool
     * @throws \Exception
     */
    public function hasAllFeatures(...$features): bool
    {
        if (is_array($features[0])) {
            $features = $features[0];
        }

        foreach ($features as $feature) {
            if (! $this->hasFeature($feature)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Determine if the model has, via plan, the given feature.
     *
     * @param \Wwwillian\LaravelPlans\Contracts\Feature $feature
     *
     * @return bool
     */
    protected function hasFeatureViaPlan(Feature $feature): bool
    {
        return $this->hasPlan($feature->plan);
    }

    /**
     * Determine if the model has the given feature.
     *
     * @param string|int|\Spatie\Permission\Contracts\Feature $feature
     *
     * @return bool
     */
    public function hasDirectFeature($feature): bool
    {
        $featureClass = $this->getFeatureClass();

        if (is_string($feature)) {
            $feature = $featureClass->findByName($feature);
            if (! $feature) {
                return false;
            }
        }

        if (is_int($feature)) {
            $feature = $featureClass->findById($feature);
            if (! $feature) {
                return false;
            }
        }

        if (! $feature instanceof Feature) {
            return false;
        }

        return $this->features->contains($feature->id);
    }

    /**
     * Return all the featyres the model has via plan.
     */
    public function getFeaturesViaPlans(): Collection
    {
        return $this->load('plan', 'plan.features')->sort()->values();
    }

    /**
     * Return all the features the model has, both directly and via plan.
     *
     * @throws \Exception
     */
    public function getAllFeatures(): Collection
    {
        $features = $this->features;

        if ($this->plan) {
            $features = $features->merge($this->getFeaturesViaPlan());
        }

        return $features->sort()->values();
    }

    /**
     * Grant the given feature(s) to a plan.
     *
     * @param string|array|\Wwwillian\LaravelPlans\Contracts\Feature|\Illuminate\Support\Collection $features
     *
     * @return $this
     */
    public function addFeature(...$features)
    {
        $features = collect($features)
            ->flatten()
            ->map(function ($feature) {
                if (empty($feature)) {
                    return false;
                }

                return $this->getStoredFeature($feature);
            })
            ->filter(function ($feature) {
                return $feature instanceof Feature;
            })
            ->map->id
            ->all();

        $model = $this->getModel();

        if ($model->exists) {
            $this->features()->sync($features, false);
            $model->load('features');
        } else {
            $class = \get_class($model);

            $class::saved(
                function ($object) use ($features, $model) {
                    static $modelLastFiredOn;
                    if ($modelLastFiredOn !== null && $modelLastFiredOn === $model) {
                        return;
                    }
                    $object->features()->sync($features, false);
                    $object->load('features');
                    $modelLastFiredOn = $object;
                }
            );
        }

        $this->forgetCachedFeatures();

        return $this;
    }

    /**
     * Remove all current features and set the given ones.
     *
     * @param string|array|\Wwwillian\LaravelPlans\Contracts\Feature|\Illuminate\Support\Collection $features
     *
     * @return $this
     */
    public function syncFeatures(...$features)
    {
        $this->features()->detach();

        return $this->giveFeatureTo($features);
    }

    /**
     * Revoke the given feature.
     *
     * @param \Wwwillian\LaravelPlans\Contracts\Feature|\Wwwillian\LaravelPlans\Contracts\Feature[]|string|string[] $feature
     *
     * @return $this
     */
    public function revokeFeatureTo($feature)
    {
        $this->features()->detach($this->getStoredFeature($feature));

        $this->forgetCachedFeatures();

        $this->load('features');

        return $this;
    }

    public function getFeatureNames(): Collection
    {
        return $this->features->pluck('name');
    }

    /**
     * @param string|array|\Wwwillian\LaravelPlans\Contracts\Feature|\Illuminate\Support\Collection $features
     *
     * @return \Wwwillian\LaravelPlans\Contracts\Feature|\Wwwillian\LaravelPlans\Contracts\Feature[]|\Illuminate\Support\Collection
     */
    protected function getStoredFeature($features)
    {
        $featureClass = $this->getFeatureClass();

        if (is_numeric($features)) {
            return $featureClass->findById($features);
        }

        if (is_string($features)) {
            return $featureClass->findByName($features);
        }

        if (is_array($features)) {
            return $featureClass
                ->whereIn('name', $features)
                ->get();
        }

        return $features;
    }

    /**
     * Forget the cached features.
     */
    public function forgetCachedFeatures()
    {
        app(FeatureRegistrar::class)->forgetCachedFeatures();
    }

    /**
     * Check if can use the current feature.
     *
     * @return bool
     */
    public function can()
    {
        if($this->checkFeature($this->featureName))
        {
            $featureClass = $this->getFeatureClass();
            $feature = $featureClass->findByName($featureName);

            return method_exists($feature, 'rule') ? $feature->rule() : true;
        }
        return false;
    }
}

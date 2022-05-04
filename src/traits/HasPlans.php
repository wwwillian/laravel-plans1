<?php

namespace MsAlvexx\LaravelPlans\Traits;

use Illuminate\Support\Collection;
use MsAlvexx\LaravelPlans\Contracts\Plan;
use Illuminate\Database\Eloquent\Builder;
use MsAlvexx\LaravelPlans\Providers\FeatureRegistrar;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

trait HasPlans
{
    use HasFeatures;

    private $planClass;

    public static function bootHasPlan()
    {
        static::deleting(function ($model) {
            if (method_exists($model, 'isForceDeleting') && ! $model->isForceDeleting()) {
                return;
            }

            $model->plan()->detach();
        });
    }

    public function getPlanClass()
    {
        if (! isset($this->planClass)) {
            $this->planClass = app(FeatureRegistrar::class)->getPlanClass();
        }

        return $this->planClass;
    }

    /**
     * A model can have one plan.
     */
    public function plan(): BelongsTo
    {
        return $this->belongsTo(
            config('plans.models.plan')
        );
    }

    /**
     * A user can have many features.
     */
    public function features()
    {
        return $this->plan->features();
    }

    /**
     * Assign the given plan to the model.
     *
     * @param array|string|\MsAlvexx\LaravelPlans\Contracts\Plan $plan
     *
     * @return $this
     */
    public function assignPlan($plan)
    {
        if (gettype($plan) == "string") {
            $plan = $this->getStoredPlan($plan);
        }

        $model = $this->getModel();

        if ($model->exists) {
            $this->plan()->associate($plan);
            $this->save();
            $model->load('plan');
        }

        $this->forgetCachedFeatures();

        return $this;
    }

    /**
     * Change current plan to another.
     *
     * @param array|\MsAlvexx\LaravelPlans\Contracts\Plan|string $plan
     *
     * @return $this
     */
    public function changePlan($plan)
    {
        $this->plan()->dissociate();

        return $this->assignPlan($plan);
    }

    /**
     * Determine if the model has the given plan.
     *
     * @param string|\MsAlvexx\LaravelPlans\Contracts\Plan
     *
     * @return bool
     */
    public function hasPlan($plan): bool
    {
        if (is_string($plan)) {
            return $this->plan->name ==  $plan;
        }

        if (is_int($plan)) {
            return $this->plan->id == $plan;
        }

        if ($plan instanceof Plan) {
            return $this->plan->id ==  $plan->id;
        }

        return false;
    }

    /**
     * Determine if the model has (one of) the given plan(s).
     *
     * @param string|int|array|\MsAlvexx\LaravelPlans\Contracts\Plan|\Illuminate\Support\Collection $plan
     *
     * @return bool
     */
    public function hasAnyPlan($plans): bool
    {
        if (is_string($plans) && false !== strpos($plans, '|')) {
            $plans = $this->convertPipeToArray($plans);
        }

        if (is_string($plans)) {
            return $this->plan->name == $plans;
        }

        if (is_int($plans)) {
            return $this->plan->id == $plans;
        }

        if ($plans instanceof Plan) {
            return $this->plan->id == $plan->id;
        }

        if (is_array($plans)) {
            foreach ($plans as $plan) {
                if ($this->hasAnyPlan($plan)) {
                    return true;
                }
            }

            return false;
        }

        return false;
    }

    public function getPlanName(): string
    {
        return $this->plan->name;
    }

    protected function getStoredPlan($plan): Plan
    {
        $planClass = $this->getPlanClass();

        if (is_numeric($plan)) {
            return $planClass->findById($plan);
        }

        if (is_string($plan)) {
            return $planClass->findByName($plan);
        }

        return $plan;
    }

    protected function convertPipeToArray(string $pipeString)
    {
        $pipeString = trim($pipeString);

        if (strlen($pipeString) <= 2) {
            return $pipeString;
        }

        $quoteCharacter = substr($pipeString, 0, 1);
        $endCharacter = substr($quoteCharacter, -1, 1);

        if ($quoteCharacter !== $endCharacter) {
            return explode('|', $pipeString);
        }

        if (! in_array($quoteCharacter, ["'", '"'])) {
            return explode('|', $pipeString);
        }

        return explode('|', trim($pipeString, $quoteCharacter));
    }
}

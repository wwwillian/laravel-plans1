<?php

namespace MsAlvexx\LaravelPlans\Models;

use Illuminate\Database\Eloquent\Model;
use MsAlvexx\LaravelPlans\Traits\HasFeatures;
use MsAlvexx\LaravelPlans\Exceptions\RoleDoesNotExist;
use MsAlvexx\LaravelPlans\Exceptions\RoleAlreadyExists;
use MsAlvexx\LaravelPlans\Contracts\Plan as PlanContract;
use MsAlvexx\LaravelPlans\Traits\RefreshesFeatureCache;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Plan extends Model implements PlanContract
{
    use HasFeatures;
    use RefreshesFeatureCache;

    protected $guarded = ['id'];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->setConnection(config('plans.connection', config('database.default')));
    }

    public static function create(array $attributes = [])
    {
        if (static::where('name', $attributes['name'])->first()) {
            throw PlanAlreadyExists::create($attributes['name']);
        }

        return static::query()->create($attributes);
    }

    /**
     * A plan may be given various features.
     */
    public function features(): BelongsToMany
    {
        return $this->belongsToMany(
            config('plans.models.feature'),
            'plan_has_features',
            'plan_id',
            'feature_id'
        );
    }

    /**
     * A role belongs to some users of the model associated with its guard.
     */
    public function users(): HasMany
    {
        return $this->hasMany(
            config('plans.models.model')
        );
    }

    /**
     * Find a plan by its name.
     *
     * @param string $name
     *
     * @return \MsAlvexx\LaravelPlans\Contracts\Plan|\MsAlvexx\LaravelPlans\Models\Plan
     *
     * @throws \MsAlvexx\LaravelPlans\Exceptions\PlanDoesNotExist
     */
    public static function findByName(string $name): PlanContract
    {
        $plan = static::where('name', $name)->first();

        if (! $plan) {
            throw PlanDoesNotExist::named($name);
        }

        return $plan;
    }

    public static function findById(int $id): PlanContract
    {
        $plan = static::where('id', $id)->first();

        if (! $plan) {
            throw PlanDoesNotExist::withId($id);
        }

        return $plan;
    }

    /**
     * Find or create plan by its name.
     *
     * @param string $name
     *
     * @return \MsAlvexx\LaravelPlans\Contracts\Plan
     */
    public static function findOrCreate(string $name): PlanContract
    {
        $plan = static::where('name', $name)->first();

        if (! $plan) {
            return static::query()->create([ 'name' => $name ]);
        }

        return $plan;
    }

    /**
     * Determine if the user may perform the given feature.
     *
     * @param string|Feature $feature
     *
     * @return bool
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

        return $this->features->contains('id', $feature->id);
    }
}

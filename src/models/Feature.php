<?php

namespace Wwwillian\LaravelPlans\Models;

use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Model;
use Wwwillian\LaravelPlans\Traits\HasPlans;
use Wwwillian\LaravelPlans\Providers\FeatureRegistrar;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Wwwillian\LaravelPlans\Exceptions\FeatureAlreadyExists;
use Wwwillian\LaravelPlans\Exceptions\FeatureDoesNotExists;
use Wwwillian\LaravelPlans\Contracts\Feature as FeatureContract;

class Feature extends Model implements FeatureContract
{
    use HasPlans;

    protected $guarded = ['id'];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->setConnection(config('plans.connection', config('database.default')));
    }

    public static function create(array $attributes = [])
    {
        $feature = static::getFeatures(['name' => $attributes['name']])->first();

        if ($feature) {
            throw FeatureAlreadyExists::create($attributes['name']);
        }

        return static::query()->create($attributes);
    }

    /**
     * A feature can be applied to plans.
     */
    public function plans(): BelongsToMany
    {
        return $this->belongsToMany(
            config('plans.models.plan'),
            'plan_has_features',
            'feature_id',
            'plan_id'
        );
    }

    /**
     * Find a feature by its name.
     *
     * @param string $name
     *
     * @throws \Wwwillian\LaravelPlans\Exceptions\FeatureDoesNotExist
     *
     * @return \Wwwillian\LaravelPlans\Contracts\Feature
     */
    public static function findByName(string $name): FeatureContract
    {
        $feature = static::getFeatures([ 'name' => $name ])->first();
        if (! $feature) {
            throw FeatureDoesNotExist::create($name);
        }

        return $feature;
    }

    /**
     * Find a feature by its id.
     *
     * @param int $id
     *
     * @throws \Wwwillian\LaravelPlans\Exceptions\FeatureDoesNotExist
     *
     * @return \Wwwillian\LaravelPlans\Contracts\Feature
     */
    public static function findById(int $id): FeatureContract
    {
        $feature = static::getFeatures([ 'id' => $id ])->first();

        if (! $feature) {
            throw FeatureDoesNotExist::withId($id, $guardName);
        }

        return $feature;
    }

    /**
     * Find or create feature by its name.
     *
     * @param string $name
     *
     * @return \Wwwillian\LaravelPlans\Contracts\Feature
     */
    public static function findOrCreate(string $name): FeatureContract
    {
        $feature = static::getFeatures([ 'name' => $name ])->first();

        if (! $feature) {
            return static::query()->create([ 'name' => $name ]);
        }

        return $feature;
    }

    /**
     * Get the current cached features.
     */
    protected static function getFeatures(array $params = []): Collection
    {
        return app(FeatureRegistrar::class)->getFeatures($params);
    }
}

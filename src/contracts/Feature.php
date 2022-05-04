<?php

namespace MsAlvexx\LaravelPlans\Contracts;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;

interface Feature
{
    /**
     * A feature can be applied to plans.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function plans(): BelongsToMany;

    /**
     * Find a feature by its name.
     *
     * @param string $name
     *
     * @throws \MsAlvexx\LaravelPlans\Exceptions\FeatureDoesNotExist
     *
     * @return Feature
     */
    public static function findByName(string $name): self;

    /**
     * Find a feature by its id.
     *
     * @param int $id
     *
     * @throws \MsAlvexx\LaravelPlans\Exceptions\FeatureDoesNotExist
     *
     * @return Feature
     */
    public static function findById(int $id): self;

    /**
     * Find or Create a feature by its name.
     *
     * @param string $name
     *
     * @return Feature
     */
    public static function findOrCreate(string $name): self;
}

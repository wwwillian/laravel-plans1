<?php

namespace MsAlvexx\LaravelPlans\Contracts;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;

interface Plan
{
    /**
     * A plan may be given various features.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function features(): BelongsToMany;

    /**
     * Find a plan by its name.
     *
     * @param string $name
     *
     * @return \MsAlvexx\LaravelPlans\Contracts\Plan
     *
     * @throws \MsAlvexx\LaravelPlans\Exceptions\PlanDoesNotExist
     */
    public static function findByName(string $name): self;

    /**
     * Find a plan by its id.
     *
     * @param int $id
     *
     * @return \MsAlvexx\LaravelPlans\Contracts\Plan
     *
     * @throws \MsAlvexx\LaravelPlans\Exceptions\PlanDoesNotExist
     */
    public static function findById(int $id): self;

    /**
     * Find or create a plan by its name.
     *
     * @param string $name
     *
     * @return \MsAlvexx\LaravelPlans\Contracts\Plan
     */
    public static function findOrCreate(string $name): self;

    /**
     * Determine if the user may perform the given feature.
     *
     * @param string|\MsAlvexx\LaravelPlans\Contracts\Feature $feature
     *
     * @return bool
     */
    public function hasFeature($feature): bool;
}

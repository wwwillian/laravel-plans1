<?php

namespace Wwwillian\LaravelPlans\Filters;

use Illuminate\Contracts\Auth\Access\Gate;
use JeroenNoten\LaravelAdminLte\Menu\Builder;
use JeroenNoten\LaravelAdminLte\Menu\Filters\FilterInterface;

class FeatureFilter implements FilterInterface
{
    protected $company;

    public function __construct()
    {
        $this->company = app('Company');
    }

    public function transform($item, Builder $builder)
    {
        if (! $this->isVisible($item)) {
            return false;
        }

        return $item;
    }

    protected function isVisible($item)
    {
        if (! isset($item['hasfeature'])) {
            return true;
        }

        return $this->company->hasFeature($item['hasfeature']);
    }
}

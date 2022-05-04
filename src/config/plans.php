<?php

return [

    'models' => [

        'model' => Wwwillian\MultiTenantDB\Models\Company::class,

        'feature' => Wwwillian\LaravelPlans\Models\Feature::class,

        'plan' => Wwwillian\LaravelPlans\Models\Plan::class,

    ],

    'table_names' => [

        'models'   => 'companies',

        'model_has_features' => 'company_has_features',

        'model_has_roles' => 'company_has_plans'
    ],

    'column_names' => [

        'model_morph_key' => 'model_id',
    ],

    'display_permission_in_exception' => false,

    'cache' => [

        'expiration_time' => \DateInterval::createFromDateString('24 hours'),

        'key' => 'wwwillian.plans.cache',

        'model_key' => 'name',

        'store' => 'default',
    ],
];

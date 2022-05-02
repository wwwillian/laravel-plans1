<?php

return [

    'models' => [

        'model' => ConnectMalves\MultiTenantDB\Models\Company::class,

        'feature' => MsAlvexx\LaravelPlans\Models\Feature::class,

        'plan' => MsAlvexx\LaravelPlans\Models\Plan::class,

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

        'key' => 'msalvexx.plans.cache',

        'model_key' => 'name',

        'store' => 'default',
    ],
];

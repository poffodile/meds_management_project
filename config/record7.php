<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Product identity
    |--------------------------------------------------------------------------
    | The same software is RECORD7 standalone and Care One OS as the integrated
    | module, so no component may hard-code the name.
    */
    'product_name' => env('RECORD7_PRODUCT_NAME', 'Record7'),
    'product_strapline' => env('RECORD7_STRAPLINE', 'Medication management and eMAR'),
    'seventh_right' => 'The Right Record',

    /*
    |--------------------------------------------------------------------------
    | Security verification
    |--------------------------------------------------------------------------
    | PROTOTYPE ONLY. The supplied Section 0 test package fixes a verification
    | code so the journey can be walked locally without a real second factor.
    | It is null unless the environment sets it, and AuthenticationService
    | refuses every code when it is null — so a deployment that forgets to
    | configure real MFA fails closed rather than accepting a known code.
    |
    | Never set this in production.
    */
    'mfa' => [
        'prototype_code' => env('RECORD7_PROTOTYPE_MFA_CODE'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Local fixture seeding
    |--------------------------------------------------------------------------
    | The Section 0 seeder refuses to run unless this is explicitly true AND
    | the environment is not production.
    */
    'allow_fixture_seed' => (bool) env('RECORD7_ALLOW_FIXTURE_SEED', false),

    'fixture_path' => database_path('fixtures/record7/record7-section0-test.sqlite'),
];

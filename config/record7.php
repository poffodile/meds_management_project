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
        /*
        |----------------------------------------------------------------------
        | Verification mode
        |----------------------------------------------------------------------
        | 'off'         The prototype default. The verification step is skipped
        |               entirely, so the journey is organisation, credentials,
        |               house, Today. This is what normal UI testing uses.
        |
        | 'test'        The verification screen appears and accepts the fixed
        |               fictional code, under a plain "not real security"
        |               label. Turn this on only to look at that screen.
        |
        | 'production'  A real verification provider is required. None is
        |               integrated yet, so this refuses everything — which is
        |               the correct behaviour for an unfinished control.
        |
        | The production ENVIRONMENT forces 'production' whatever this says, so
        | 'off' can never bypass verification on a live system.
        */
        'mode' => env('RECORD7_MFA_MODE', 'off'),

        /*
        | The fictional fixed code, for local development only.
        |
        | BOTH of these must be set before it is honoured, and neither has any
        | effect in production — AuthenticationService::prototypeCode() checks
        | the environment first and returns null there regardless. Two switches
        | rather than one so a stray environment variable cannot enable it on
        | its own.
        */
        'allow_prototype_code' => (bool) env('RECORD7_ALLOW_PROTOTYPE_MFA', false),
        'prototype_code' => env('RECORD7_PROTOTYPE_MFA_CODE'),

        /*
        | What production is expected to offer. Section 0 records the shape;
        | the drivers themselves arrive with the real integration.
        */
        'methods' => ['authenticator_app', 'passkey', 'security_key', 'hardware_otp'],

        'recovery_code_count' => 10,

        /*
        | How long a device stays trusted, when the organisation has not set
        | its own value. Each organisation overrides this on its own record,
        | and VerificationPolicy clamps whatever it finds to MAX_TRUST_DAYS so
        | nobody can set something reckless. Zero means never trust a device.
        */
        'trust_device_days' => 30,

        /*
        | What makes an account worth challenging every time.
        |
        | Decided from what a person can DO, not from a role code — so a
        | Service Manager, a Medication Lead, an Organisation Administrator and
        | a Quality and Compliance Reviewer all qualify without being named,
        | and so does a support worker who has been given an unusual extra
        | permission.
        |
        | Deliberately NOT "every sensitive permission": twelve of the thirteen
        | supplied permissions are flagged sensitive, so that test would
        | challenge everybody and mean nothing. These are the ones where an
        | impostor does damage that outlives the session.
        */
        'elevated_permissions' => [
            'manage_organisation',
            'manage_staff',
            'view_access_audit',
            'correction_approval',
            'cross_service_access',
        ],

        'elevated_access_types' => ['manager', 'oversight'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Support
    |--------------------------------------------------------------------------
    | Where "Contact support" goes. Left null, the screen explains that a
    | manager or organisation administrator is the person who can help, which
    | is true and more useful than a dead link.
    */
    'support_url' => env('RECORD7_SUPPORT_URL'),

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

<?php
return [
    /*
    |--------------------------------------------------------------------------
    | Honeypot
    |--------------------------------------------------------------------------
    |
    | Name of the hidden field used to detect automated form submissions.
    | Requests containing a non-empty value are rejected with the given
    | status code (must be a valid 4xx/5xx code, otherwise 403 is used).
    |
    | Use the "honeypot" middleware alias on routes and the @honeypot Blade
    | directive inside forms.
    |
    */
    'honeypot'            => [
        'enabled'    => true,
        'status'     => 403,
        'field_name' => env('HONEYPOT_FIELD_NAME', 'honeypot'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Jalali Date Locales
    |--------------------------------------------------------------------------
    |
    | Application locales in which JalaliDateCast converts dates between the
    | Gregorian (database) and Jalali (application) calendars.
    |
    */
    'jalali_date_locales' => ['fa'],

    /*
    |--------------------------------------------------------------------------
    | Right-to-left Locales
    |--------------------------------------------------------------------------
    |
    | Primary language subtag treated as right-to-left by the is_rtl() helper.
    |
    */
    'rtl_locales'         => ['ar', 'fa', 'he', 'ur', 'ps', 'ku', 'ckb', 'sd', 'yi', 'dv', 'ug'],

    /*
    |--------------------------------------------------------------------------
    | Password Rule
    |--------------------------------------------------------------------------
    |
    | Length limits enforced by PasswordRule. The maximum protects the hasher
    | from excessively long inputs.
    |
    */
    'password'            => [
        'min_length' => 8,
        'max_length' => 128,
    ],


    /*
    |--------------------------------------------------------------------------
    | Static Data Path
    |--------------------------------------------------------------------------
    |
    | Directory (relative to base_path()) that getStaticData() may read PHP
    | data files from. Files outside this directory are never loaded, because
    | the helper executes the files it resolves.
    |
    */
    'data_path'           => 'app/data',
];

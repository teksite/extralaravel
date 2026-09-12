<?php
return [
    /*
    |--------------------------------------------------------------------------
    | Honeypot
    |--------------------------------------------------------------------------
    |
    | Name of the hidden field used to detect automated form submissions.
    | Requests containing a non-empty value are rejected.
    |
    */
    'honeypot' => [
        'enabled'    => true,
        'status'     => 403,
        'field_name' => env('HONEYPOT_FIELD_NAME', 'honeypot'),
    ],

    'jalalit_date_locales' => ['fa',],

    'rtl_locales' => ['ar', 'fa', 'he', 'ur', 'ps', 'ku', 'sd', 'yi'],


    'data_path' => '/app/data',

];

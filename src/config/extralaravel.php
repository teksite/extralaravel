<?php
return [
    /*
    |--------------------------------------------------------------------------
    | CMS CONFIG
    |--------------------------------------------------------------------------
    |
    |Add a hidden input field below the name input to indicate whether the field is filled;
    | use it to prevent form submission if it's empty
    */
    'honeypot'      => [
        'field_name' => env('HONEYPOT_FIELD_NAME', 'honeypot'),
    ],
    'get_Data_path' => '/app/data',
];

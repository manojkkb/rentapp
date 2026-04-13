<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Vendor area PWA (install prompt / home screen label)
    |--------------------------------------------------------------------------
    |
    | Shown in the web app manifest and Apple meta tags. Independent of APP_NAME
    | so installs show "Rentkia App" even when APP_NAME is still "Laravel".
    |
    */

    'vendor' => [
        'name' => env('VENDOR_PWA_NAME', 'Rentkia App'),
        'short_name' => env('VENDOR_PWA_SHORT_NAME', 'Rentkia App'),
    ],

];

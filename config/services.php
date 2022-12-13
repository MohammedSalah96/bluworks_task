<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Stripe, Mailgun, SparkPost and others. This file provides a sane
    | default location for this type of information, allowing packages
    | to have a conventional place to find your various credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
    ],

    'ses' => [
        'key' => env('SES_KEY'),
        'secret' => env('SES_SECRET'),
        'region' => 'us-east-1',
    ],

    'sparkpost' => [
        'secret' => env('SPARKPOST_SECRET'),
    ],

    'stripe' => [
        'model' => App\User::class,
        'key' => env('STRIPE_KEY'),
        'secret' => env('STRIPE_SECRET'),
    ],

    'facebook' => [
        'client_id' => '348887996313237',         
        'client_secret' => '', 
        'redirect' => '',
    ],
    'google' => [
        'client_id' => '547567848746-2qdt39trustsadvqf1ijl5vb5p467goc.apps.googleusercontent.com',
        'client_secret' => '-QnQ1L2ruFnF930K0DkzeKxa',
        'redirect' => '',
    ],
    'apple' => [
        'client_id' => 'com.spoon.applesocial',
        'client_secret' => '',
        'redirect' => '',
    ],

];

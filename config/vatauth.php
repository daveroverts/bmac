<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Redirect After Login
    |--------------------------------------------------------------------------
    |
    | This is where Vatauth should redirect users after they have logged in
    | successfully using Vatsim SSO. This can either be a URK or a named
    | Laravel route (change the type setting to reflect this).
    |
    | Supported: "url", "route"
    |
    */
    'redirect' => [
        'afterLogin' => [
            'type' => 'url',
            'to' => '/home'
        ],
        'afterLogout' => [
            'type' => 'url',
            'to' => '/'
        ]
    ],

    /*
    |--------------------------------------------------------------------------
    | OAuth SSO Server
    |--------------------------------------------------------------------------
    |
    | This is the URL of the VATSIM OAuth server that Vatauth should use. By
    | default, this inline conditional code will either select the VATSIM
    | CERT server if the application is running in production mode, or
    | the demo OAuth server which is hosted by Hardern for testing.
    |
    */
	'server' => env('APP_ENV', 'production') == 'production' ? 'https://cert.vatsim.net/sso/' :  'http://sso.hardern.net/server/',
    
    /*
    |--------------------------------------------------------------------------
    | VATSIM SSO Client Key and Secret
    |--------------------------------------------------------------------------
    |
    | These are the client credentials that Vatauth will use when sending auth
    | requests. Like all sensitive data, please keep them in your .env file
    | (under the SSO_KEY and SSO_SECRET keys). If these .env keys aren't
    | found, then Hardern's demo vACC level credentials will be used.
    | 
    | More info: https://forums.vatsim.net/viewtopic.php?f=134&t=65319
	|
    */
    'key' => env('SSO_KEY', 'SSO_DEMO_VACC'),
    'secret' => env('SSO_SECRET', '04i_~ruVUE.1-do1--sc'),
	
    /*
    |--------------------------------------------------------------------------
    | VATSIM SSO Client Authentication Method
    |--------------------------------------------------------------------------
    |
    | This will set the authentication method that Vatauth will use for auth
    | requests. VATSIM has instructed that RSA be used unless this is not
    | possible, thus the default value is RSA. If you really can't use
    | RSA, then you can use HMAC by adding an SSO_METHOD key in the
    | .env file. If you're using RSA, don't forget your cert.key 
    |
    | Supported: "RSA", "HMAC"
	|
    */
	'method' => env('SSO_METHOD', 'RSA'),

    /*
    |--------------------------------------------------------------------------
    | Laravel User Eloquent Model
    |--------------------------------------------------------------------------
    |
    | Change this is you have moved or renamed your User Eloquent model. The
	| default value is the default Laravel User Eloquent model class in App.
	|
    */
    'users' => [
            'model' => App\User::class,
    ],

];


<?php
/*
 * DO NOT PUBLISH THE KEY, SECRET AND CERT TO CODE REPOSITORIES
 * FOR SECURITY. PLEASE USE LARAVEL'S .envFILES TO PROTECT
 * SENSITIVE DATA.
 * http://laravel.com/docs/master/configuration#environment-configuration
 *
 *
 * Modify the three constants below to match the keys in your .env, otherwise it will use what you enter
 * on the second line of the key/secret/cert elements
 */

return [

    /*
     * The location of the OAuth interface
     */
    'base' => env('OAUTH_BASE', 'https://auth.vatsim.net'),

    /*
     * The consumer key for your organisation (provided by VATSIM)
     */
    'id' => env('OAUTH_CLIENT'),

    /*
    * The secret key for your organisation (provided by VATSIM)
    * Do not give this to anyone else or display it to your users. It must be kept server-side
    */
    'secret' => env('OAUTH_SECRET'),

    /**
     * The scopes the user will be requested.
     */
    'scopes' => explode(',', 'full_name,email'),

    /*
     * OAuth variable mapping
     */
    'mapping_cid' => env('OAUTH_MAPPING_CID', 'data-cid'),
    'mapping_first_name' => env('OAUTH_MAPPING_FIRSTNAME', 'data-personal-name_first'),
    'mapping_last_name' => env('OAUTH_MAPPING_LASTNAME', 'data-personal-name_last'),
    'mapping_mail' => env('OAUTH_MAPPING_EMAIL', 'data-personal-email'),

];

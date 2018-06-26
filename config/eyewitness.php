<?php

return [
    /*
     |--------------------------------------------------------------------------
     | Base Eyewitness URI
     |
     | https://docs.eyewitness.io/configuration/general#base-url
     |--------------------------------------------------------------------------
     |
     | This is the base URI for all routes used by eyewitness. By default it
     | will be: www.yourdomain.com/eyewitness
     |
     | If you want it to be something else to prevent clashes or for obscurity
     | reasons you can change it below.
     |
     */

    'base_uri' => 'eyewitness',

    /*
     |--------------------------------------------------------------------------
     | Display Eyewitness helper information
     |
     | https://docs.eyewitness.io/configuration/general#eyewitness-helper
     |--------------------------------------------------------------------------
     |
     | Throughout the package we will display introductory/helper information on
     | each page to help you navigate your way around. Once you are familiar
     | with Eyewitness you can turn these off.
     |
     */

    'display_helpers' => true,

    /*
     |--------------------------------------------------------------------------
     | Eyewitness history
     |
     | https://docs.eyewitness.io/configuration/general#eyewitness-history
     |--------------------------------------------------------------------------
     |
     | This is how long Eyewitness will keep history relating to your queues,
     | cron schedulers etc. We recommend 45 days, but you are welcome to change
     | this to be longer or shorter to suit your needs.
     |
     */

    'days_to_keep_history' => 45,

    /*
     |--------------------------------------------------------------------------
     | Configure standard monitoring modules
     |
     \ https://docs.eyewitness.io/configuration/general#configure-standard-monitoring-modules
     |--------------------------------------------------------------------------
     |
     | You can turn off certain parts of the monitoring. For example, if your
     | application does not use queues, you should turn that off.
     |
     */

    'monitor_scheduler' => true,
    'monitor_database' => true,
    'monitor_composer' => true,
    'monitor_queue' => true,
    'monitor_dns' => true,
    'monitor_ssl' => true,

    /*
     |--------------------------------------------------------------------------
     | Custom monitors
     |
     | https://docs.eyewitness.io/monitors/custom
     |--------------------------------------------------------------------------
     |
     | If you want to load any Custom monitors that you have created via the
     | "make:witness" command - just register them here.
     |
     | Please read the documentation for full instructions on creating your own
     | custom monitor
     |
     */

    'custom_witnesses' => [],

    /*
     |--------------------------------------------------------------------------
     | Capture cron scheduler output
     |
     | https://docs.eyewitness.io/configuration/general#capture-cron-scheduler-output
     |--------------------------------------------------------------------------
     |
     | If your cron schedulers are being monitored, then you can also have the
     | output from the cron job captured and stored by Eyewitness. This allows
     | you to view the output online at a later stage if needed.
     |
     | If you set this to false, the output will not be captured or stored.
     |
     */

    'capture_cron_output' => env('EYEWITNESS_CAPTURE_CRON_OUTPUT', true),

    /*
     |--------------------------------------------------------------------------
     | Domains
     |
     | https://docs.eyewitness.io/configuration/general#domains
     |--------------------------------------------------------------------------
     |
     | Here you can set the domain(s) of your application. This will be used to
     | monitor the DNS and SSL certificates for this application.
     |
     */

    'application_domains' => [
        env('APP_URL'),
    ],

    /*
     |--------------------------------------------------------------------------
     | Databases
     |
     | https://docs.eyewitness.io/configuration/general#databases
     |--------------------------------------------------------------------------
     |
     | If your application uses multiple databases, then you can list them below
     | and each will be monitored to ensure they are online and available.
     |
     | The array should contain names corresponding to one of the connections
     | listed in your "config/database.php" configuration file.
     |
     | *Note:* an empty array value will simply use the "default" connection,
     | which is sufficient for most applications.
     |
     */

    'database_connections' => [],

    /*
     |--------------------------------------------------------------------------
     | Composer.lock location
     |
     | https://docs.eyewitness.io/configuration/general#composer.lock
     |--------------------------------------------------------------------------
     |
     | A daily check of your composer.lock file will occur against the SensioLabs
     | Security check at https://security.sensiolabs.org/
     |
     | The below is the location of your composer.lock file. You only need to
     | modify this config if your lock file is in a different location than
     | the default location.
     |
     */

    'composer_lock_file_location' => base_path('composer.lock'),

    /*
     |--------------------------------------------------------------------------
     | Eyewitness database storage connection
     |
     | https://docs.eyewitness.io/configuration/general#eyewitness-database-storage-connection
     |--------------------------------------------------------------------------
     |
     | Eyewitness will create a number of tables to store data it collects. By
     | default the package will use your normal database connection - but you
     | are able to specify a specific connection if required.
     |
     | This means you can keep the Eyewitness data separate from your application,
     | and separates your backups, migrations etc.
     |
     */

    'eyewitness_database_connection' => env('EYEWITNESS_DATABASE_CONNECTION', null),

    /*
     |--------------------------------------------------------------------------
     | Allow scheduler to run Eyewitness tasks in background
     |
     | https://docs.eyewitness.io/configuration/general#disable-scheduler-background-tasks
     |--------------------------------------------------------------------------
     |
     | This should only be changed if you run Laravel & Eyewitness on a Windows server and
     | experience issues with your schedules not running correctly. This is caused by some
     | inconsistencies in how Window servers handles background processes. Only in this
     | specific situation should you make the following change.
     |
     */

    'enable_scheduler_background' => true,

    /*
     |--------------------------------------------------------------------------
     | Web route middleware
     |
     | https://docs.eyewitness.io/configuration/general#web-route-middleware
     |--------------------------------------------------------------------------
     |
     | Here you can set what route middleware Eyewitness should use. On most
     | applications it will be the default "web" middleware name, but if you
     | use a custom name you will need to set it here first.
     |
     */

    'route_middleware' => 'web',

    /*
     |--------------------------------------------------------------------------
     | Application token & secret key
     |
     | https://docs.eyewitness.io/getting-started/installation
     |--------------------------------------------------------------------------
     |
     | Your unique Eyewitness.io application token & secret key. These settings
     | will be automatically set by the installer when you first run
     | 'artisan eyewitness:install' and placed in your ".env" file.
     |
     | You can all generate a new token and secret key automatically by running
     | 'artisan eyewitness:regenerate'.
     |
     | The `subscription_token` can be left blank, unless you are given a specific
     | key when using the remote eyewitness subscription: https://eyewitness.io/remote
     |
     */

    'app_token' => env('EYEWITNESS_APP_TOKEN'),
    'secret_key' => env('EYEWITNESS_SECRET_KEY'),
    'subscription_key' => env('EYEWITNESS_SUBSCRIPTION_KEY'),

    /*
     |--------------------------------------------------------------------------
     | Eyewitness.io API URL
     |--------------------------------------------------------------------------
     |
     | This is the URL for the Eyewitness.io API servers. This should be left as
     | the default unless you are asked to change it by our support team.
     |
     */

    'api_url' => env('EYEWITNESS_API_URL', 'https://eyew.io/api/v3'),

    /*
     |--------------------------------------------------------------------------
     | Eyewitness.io Debugger
     |--------------------------------------------------------------------------
     |
     | Turning this on will allow Eyewitness to log and capture additional alerts
     | beyond what it normally captures. This is different from your application
     | debug setting. You should not normally need to enable this unless asked
     | by our support team.
     |
     */

    'debug' => env('EYEWITNESS_DEBUG', false),
];

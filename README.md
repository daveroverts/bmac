# About this project
Book me a Cookie is a booking system created in Laravel. It's initial purpose was to be used for one event (The Holland - America Line), howerver, this system is already ready to be used for more events.

# Features
- Uses [Vatsim SSO](https://forums.vatsim.net/viewforum.php?f=134)
   - At the moment, only VACC tokens are supported, but this will be changed soon

# Installing
Before you begin, make sure you have a server to run everything on. For local development, I use [Devilbox](http://devilbox.org/).
1. Clone the repository
2. Copy ``env-example`` to ``.env``. The following must be changed
    - APP_ENV:
        - ``production`` Vatsim SSO will be normally used, don't do this when testing.
        - Anything else will use the demo server
    - APP_URL: Be sure to set this, if you don't, SSO will redirect you incorrectly.
    - DB_*: As required
    - QUEUE_DRIVER: I recommend either to use ``database`` or ``redis``, but this depends on your setup.
    - MAIL_DRIVER: For testing, use something like [Mailtrap](https://mailtrap.io/)
    - MAIL_FROM_ADDRESS: For testing, this can be anything.
    - MAIL_FROM_NAME: This will be used as the ``From`` name.
    - SSO_*: On production, fill you SSO credentials in. For the demo server, use [these](https://pastebin.com/AYYDVdqc)
3. Run the following commands: 
    - Production:   ``composer install --optimize-autoloader --no-dev``
    - Development:  ``composer install``
    - ``php artisan key:generate``
    - ``php artisan migrate``
    - ``php artisan storage:link``
4. Open the website, and login.
5. Open the database, and make yourself admin by setting ``isAdmin`` to ``1``.
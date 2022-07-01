
# Book me a Cookie [BMAC]

![CI](https://github.com/daveroverts/bmac/workflows/CI/badge.svg)

Book me a Cookie [BMAC] is a Vatsim booking system created in Laravel.
It's initial purpose was to be used for one event (The Holland - America Line),
however, this system is already ready to be used for more events.

## Features

- Uses [Vatsim Connect](https://vatsimnetwork.github.io/documentation/connect) as
  default authentication provider
- A different OAuth2 provider can also be used
- Supports the following event types:
  - One-way / Groupflight
  - Citypair
  - Fly-in
  - Multiflights (limited to 2 flights per booking)
- E-mail templates included
- Bootstrap / Bootswatch colors are editable
- Slots can be imported or added manually.
- Airports and Events can have links for charts, briefing, scenery or something else.
  They are visible after pilots book a flight.

## Tech Stack

- Laravel
- Bootstrap 4
- Bootswatch 4 Flatly theme

## Vatsim Connect

In order to use Vatsim Connect as OAuth2 provider, you need to create a
organization (or have somebody else do that for you). <https://auth.vatsim.net/>

Once you have a Organization, Navigate to `OAUTH`,
create `NEW SITE` and set the `Redirect URL` to the `APP_URL` + `/login`.
For example: `https://example.org/login`.

Save the `Client ID` and `Secret` somewhere, you will need this later.

When testing or running this project locally, Vatsim wants your to use their
Connect Development Environment. Details can be found here: <https://github.com/vatsimnetwork/developer-info/wiki/Connect-Development-Environment>

## Installation

Before you begin, make sure you have a server with PHP 8.1 to run everything on.
For local development,
I use [Laravel Valet](https://laravel.com/docs/9.x/valet),
and before that I used [Laravel Homestead](https://laravel.com/docs/9.x/homestead).

1. Clone the project

   ```bash
     git clone -b main https://github.com/daveroverts/bmac.git
   ```

2. Go to the project directory

   ```bash
     cd bmac
   ```

3. Copy `.env.example` to `.env`

   ```bash
     cp .env.example .env
   ```

   Open `.env`. The following must be changed:

   - `APP_ENV`
     - Set this to `production` when running this in a production environment.
     - Set this to `local` when running this project locally to test things out.
   - `APP_URL`
     - Be sure to set this to the URL the project will be running. For example: ``APP_URL=https://example.org``
     - If you forget, you will have issues with Vatsim Connect (or any OAuth 2 provider)
   - `BUGSNAG_API_KEY`:
     - BMAC uses Bugsnag by default for error monitoring.
     - If you have a key, you can put this here.
   - `SENTRY_LARAVEL_DSN`:
     - If you prefer to use Sentry, you can fill in the DSN here.
   - `DB_*`
     - As required
     - If you need to share a database with some other application,
     you can add in a prefix by setting `DB_TABLE_PREFIX=bmac_`
     - If your database does not support long indexes, set `DB_LOWER_STRING_LENGTH=true`
   - `QUEUE_CONNECTION`
     - For local, you can use `sync` with no issues
     - In a production environment, I recommend you use something else,
     like `database` or `redis`. More info can be found [here](https://laravel.com/docs/9.x/queues)
       - When you use `database`, the `jobs` table is already migrated,
       no need to do that again.
       - When you use `redis`, and can't use `phpredis` PHP extension,
       `predis` is already in the `composer.json` file,
       no need to require it again. You do need to add `REDIS_CLIENT=predis`.
       See this link for more information about Redis and Laravel: <https://laravel.com/docs/9.x/redis#introduction>
   - `MAIL_*`
     - As required
     - `MAIL_MAILER`: For testing, you can use something like
     [Mailtrap](https://mailtrap.io/) (online) or
     [Mailhog](https://github.com/mailhog/MailHog)
     (local, included with [Laravel Homestead](https://laravel.com/docs/9.x/homestead))
     - `MAIL_FROM_ADDRESS`: This will be used as the `From` email.
     Don't forget to set this.
     - `MAIL_FROM_NAME`: This will be used as the `From` name
   - `OAUTH_*`
     - See [Vatsim Connect](#vatsim-connect) if you're not sure what to do
     at this point.
   - `SITE_*`
     - Feel free to edit these. They are used all over the place.
   - `BOOTSTRAP_COLOR`:
     - By default, BMAC uses [Bootswatch Flatly](https://bootswatch.com/flatly/).
     If you wish to edit some colors, you can do so here.

4. Install dependencies

   Production:

   ```bash
     composer install --optimize-autoloader --no-dev
     php artisan key:generate # Only needed for first deployment
     php artisan migrate
     php artisan storage:link # Only needed for first deployment
     npm ci
     npm run prod
   ```

   Development:

   ```bash
     composer install
     php artisan key:generate # Only needed for first deployment
     php artisan migrate
     php artisan storage:link # Only needed for first deployment
     npm ci
     npm run dev
   ```

5. Open the website, and login.

6. Open the database, and make yourself admin by setting `isAdmin` to `1`.

7. Setup Task schedule. You need to add a cronjob to run
   `php artisan schedule:run` every minute. Example can be found below:

    ```bash
      * * * * * cd /bmac && php artisan schedule:run >> /dev/null 2>&1
    ```

    For local development,
    you can run `php artisan schedule:work` in a separate terminal.

    More info can be found here: <https://laravel.com/docs/9.x/scheduling#running-the-scheduler>

8. (Optional) If you want to include all airports in the database,
run the following command:

   ```bash
     php artisan import:airports
   ```

    The script uses [this](https://raw.githubusercontent.com/mborsetti/airportsdata/main/airportsdata/airports.csv)
    file as source.
    If you choose to not include all airports,
    you're responsible to add the ones you need.
    If you're planning on importing flights later on,
    add the airports in first before starting a import.

## Queue worker / Laravel Horizon

If you're not using `sync` as `QUEUE_CONNECTION`, you need to run a queue worker,
or else things like emails aren't being sent.
Check Laravel documentation on how to set one up using Supervisor <https://laravel.com/docs/9.x/queues#supervisor-configuration>

When you're using `redis` as `QUEUE_CONNECTION`, [Laravel Horizon](https://laravel.com/docs/9.x/horizon)
is already installed and can be used to start a queue worker.

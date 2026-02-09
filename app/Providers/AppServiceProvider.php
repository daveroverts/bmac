<?php

namespace App\Providers;

use App\Http\View\Composers\EventsComposer;
use App\Models\Airport;
use App\Models\AirportLink;
use App\Models\Booking;
use App\Models\Event;
use App\Models\Faq;
use App\Policies\AirportLinkPolicy;
use App\Policies\AirportPolicy;
use App\Policies\BookingPolicy;
use App\Policies\EventPolicy;
use App\Policies\FaqPolicy;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (config('database.lower_string_length')) {
            Schema::defaultStringLength(191);
        }

        Paginator::useBootstrap();

        $this->configureAuth();
        $this->configureRateLimiting();
        $this->configureViews();

        require base_path('routes/breadcrumbs.php');
    }

    /**
     * Configure authentication policies and gates.
     */
    protected function configureAuth(): void
    {
        Gate::policy(Airport::class, AirportPolicy::class);
        Gate::policy(AirportLink::class, AirportLinkPolicy::class);
        Gate::policy(Booking::class, BookingPolicy::class);
        Gate::policy(Event::class, EventPolicy::class);
        Gate::policy(Faq::class, FaqPolicy::class);

        Gate::before(function ($user, $ability) {
            if ($user->isAdmin) {
                return true;
            }
        });
    }

    /**
     * Configure the rate limiters for the application.
     */
    protected function configureRateLimiting(): void
    {
        RateLimiter::for('api', fn (Request $request) => Limit::perMinute(60)->by($request->user()?->id ?: $request->ip()));
    }

    /**
     * Configure view composers.
     */
    protected function configureViews(): void
    {
        View::composer('layouts.navbar', EventsComposer::class);
    }
}

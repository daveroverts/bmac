<?php

namespace App\Providers;

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
use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Laravel\Passport\Passport;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        Airport::class => AirportPolicy::class,
        AirportLink::class => AirportLinkPolicy::class,
        Booking::class => BookingPolicy::class,
        Event::class => EventPolicy::class,
        Faq::class => FaqPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        Passport::routes();

        Gate::before(function ($user, $ability) {
            if ($user->isAdmin) {
                return true;
            }
        });
    }
}

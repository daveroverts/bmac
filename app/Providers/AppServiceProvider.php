<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\DB;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        if (env('DB_LOWER_STRING_LENGTH')) {
            Schema::defaultStringLength(191);
        }

        Paginator::useBootstrap();
        $query = DB::table('poll')->where('hidden','0');
        $result = $query->first();
        view()->share('pollOpen',$result);
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}

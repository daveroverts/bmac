<?php

namespace App\Http\Middleware;

use Closure;
use Sentry\State\Scope;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SentryUser
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check() && app()->bound('sentry')) {
            \Sentry\configureScope(function (Scope $scope): void {
                $scope->setUser([
                    'id'    => Auth::user()->id,
                    'email' => Auth::user()->email,
                    'name'  => Auth::user()->full_name,
                ]);
            });
        }

        return $next($request);
    }
}

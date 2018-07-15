<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\{
    Support\Facades\Auth, Support\Facades\Session
};

class IsLoggedIn
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (Auth::check()) {
            return $next($request);
        }
        Session::flash('type', 'danger');
        Session::flash('title', 'Nope');
        Session::flash('message', 'You need to be logged in before you can do that');
        return redirect()->back();
    }
}

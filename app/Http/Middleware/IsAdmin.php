<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\{
    Support\Facades\Auth, Support\Facades\Session
};

class IsAdmin
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
        if (Auth::check() && Auth::user()->isAdmin) {
            return $next($request);
        }
        Session::flash('type', 'danger');
        Session::flash('title', 'Nope');
        Session::flash('message', 'You need to be logged in as a <b>Administrator</b> to do that');
        return redirect('/');
    }
}

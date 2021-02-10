<?php

namespace App\Http\Controllers;

class HomeController extends Controller
{

    /**
     * Show the homepage
     *
     * @return \Illuminate\Http\Response
     */
    public function __invoke()
    {
        $events = nextEvents(false, false, true);
        return view('home', compact('events'));
    }
}

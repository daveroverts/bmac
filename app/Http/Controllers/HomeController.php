<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        $events = nextEvents(false, false, true);
        return view('home', ['events' => $events]);
    }
}

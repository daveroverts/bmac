<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        $events = Event::query()->upcoming()->online()->onHomepage()->get();
        return view('home', ['events' => $events]);
    }
}

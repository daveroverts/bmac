<?php

namespace App\Http\Controllers;

use App\Enums\EventType;

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
        // @TODO Temporary. Maybe it's a good idea to also implement a order_by for events
        // @TODO Remove this check after EUD Event (2019-01-18)
        if ($events->first()->event_type_id == EventType::MULTIFLIGHTS) {
            $tempEvent = $events->first();
            $events->pull(0);
            $events->push($tempEvent);
        }
        return view('home', compact('events'));
    }
}

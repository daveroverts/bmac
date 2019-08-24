<?php

namespace App\Http\Controllers\Event;

use App\Http\Controllers\Controller;
use App\Models\Event;

class EventController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param Event $event
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Event $event)
    {
        return view('event.show', compact('event'));
    }
}

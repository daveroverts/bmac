<?php

namespace App\Http\Controllers\Event;

use App\Models\Event;
use Illuminate\View\View;
use App\Http\Controllers\Controller;

class EventController extends Controller
{
    public function __invoke(Event $event): View
    {
        abort_unless($event->is_online, 404);

        return view('event.show', ['event' => $event]);
    }
}

<?php

namespace App\Http\Controllers\Event;

use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\View\View;

class EventController extends Controller
{
    public function __invoke(Event $event): View
    {
        return view('event.show', compact('event'));
    }
}

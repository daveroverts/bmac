<?php

namespace App\Http\Controllers\Booking;

use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\Contracts\View\View;

class BookingImportController extends Controller
{
    public function create(Event $event): View
    {
        return view('event.admin.import', ['event' => $event]);
    }
}

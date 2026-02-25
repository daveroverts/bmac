<?php

namespace App\Http\Controllers\Booking;

use App\Http\Controllers\Controller;
use App\Http\Requests\Booking\Admin\RouteAssign;
use App\Imports\FlightRouteAssign;
use App\Models\Event;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class BookingRouteAssignController extends Controller
{
    public function create(Event $event): View
    {
        return view('event.admin.routeAssign', ['event' => $event]);
    }

    public function store(RouteAssign $request, Event $event): RedirectResponse
    {
        activity()
            ->by(auth()->user())
            ->on($event)
            ->log('Route assign triggered');

        $file = $request->file('file');
        (new FlightRouteAssign())->import($file);
        Storage::delete($file);

        flashMessage('success', __('Routes assigned'), __('Routes have been assigned to flights'));

        return to_route('events.bookings.index', $event);
    }
}

<?php

namespace App\Http\Controllers\Booking;

use App\Actions\Booking\AutoAssignFlightsAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Booking\Admin\AutoAssign;
use App\Models\Event;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class BookingAutoAssignController extends Controller
{
    public function create(Event $event): View
    {
        return view('event.admin.autoAssign', ['event' => $event]);
    }

    public function store(AutoAssign $request, Event $event, AutoAssignFlightsAction $action): RedirectResponse
    {
        $count = $action->handle($event, $request->only([
            'oceanicTrack1', 'route1', 'oceanicTrack2', 'route2', 'minFL', 'maxFL', 'checkAssignAllFlights',
        ]));

        flashMessage('success', __('Bookings changed'), __(':count bookings have been Auto-Assigned a FL, and route', ['count' => $count]));

        return to_route('admin.events.index');
    }
}

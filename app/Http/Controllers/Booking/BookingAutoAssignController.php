<?php

namespace App\Http\Controllers\Booking;

use App\Enums\BookingStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Booking\Admin\AutoAssign;
use App\Models\Booking;
use App\Models\Event;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class BookingAutoAssignController extends Controller
{
    public function create(Event $event): View
    {
        return view('event.admin.autoAssign', ['event' => $event]);
    }

    public function store(AutoAssign $request, Event $event): RedirectResponse
    {
        $bookings = $event->bookings()
            ->with(['flights' => function ($query): void {
                $query->orderBy('ctot');
            }])
            ->unless($request->checkAssignAllFlights, function ($query): void {
                $query->where('status', BookingStatus::BOOKED->value);
            })
            ->get();

        $count = 0;
        $flOdd = $request->maxFL;
        $flEven = $request->minFL;

        DB::transaction(function () use ($bookings, $request, &$count, &$flOdd, &$flEven): void {
            /** @var Booking $booking */
            foreach ($bookings as $booking) {
                $flight = $booking->flights->first();
                $count++;
                if ($count % 2 === 0) {
                    $flight->fill([
                        'oceanicTrack' => $request->oceanicTrack2,
                        'route' => $request->route2,
                        'oceanicFL' => $flEven,
                    ]);
                    $flEven += 10;
                    if ($flEven > $request->maxFL) {
                        $flEven = $request->minFL;
                    }
                } else {
                    $flight->fill([
                        'oceanicTrack' => $request->oceanicTrack1,
                        'route' => $request->route1,
                        'oceanicFL' => $flOdd,
                    ]);
                    $flOdd -= 10;
                    if ($flOdd < $request->minFL) {
                        $flOdd = $request->maxFL;
                    }
                }

                $flight->save();
            }
        });

        flashMessage('success', __('Bookings changed'), __(':count bookings have been Auto-Assigned a FL, and route', ['count' => $count]));
        activity()
            ->by(auth()->user())
            ->on($event)
            ->withProperties(
                [
                    'Track 1' => $request->oceanicTrack1,
                    'Route 1' => $request->route1,
                    'Track 2' => $request->oceanicTrack2,
                    'Route 2' => $request->route2,
                    'count' => $count,
                ]
            )
            ->log('Flights auto-assigned');

        return to_route('admin.events.index');
    }
}

<?php

namespace App\Http\Controllers\Booking;

use App\Exports\BookingsExport;
use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class BookingExportController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Event $event, Request $request): BinaryFileResponse
    {
        activity()
            ->by(auth()->user())
            ->on($event)
            ->log('Export triggered');

        return (new BookingsExport($event, $request->vacc))->download('bookings.csv');
    }
}

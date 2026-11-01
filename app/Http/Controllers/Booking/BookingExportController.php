<?php

namespace App\Http\Controllers\Booking;

use App\Exports\BookingsExport;
use App\Http\Controllers\Controller;
use App\Models\Event;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class BookingExportController extends Controller
{
    public function __invoke(Event $event): BinaryFileResponse
    {
        activity()
            ->by(auth()->user())
            ->on($event)
            ->log('Export triggered');

        return (new BookingsExport($event))->download('bookings.csv');
    }
}

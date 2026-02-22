<?php

namespace App\Http\Controllers\Booking;

use App\Http\Controllers\Controller;
use App\Http\Requests\Booking\Admin\ImportBookings;
use App\Imports\BookingsImport;
use App\Models\Event;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;

class BookingImportController extends Controller
{
    public function create(Event $event): Factory|View
    {
        return view('event.admin.import', ['event' => $event]);
    }

    public function store(ImportBookings $request, Event $event): RedirectResponse
    {
        activity()
            ->by(auth()->user())
            ->on($event)
            ->log('Import triggered');

        $file = $request->file('file');
        (new BookingsImport($event))->import($file);
        Storage::delete($file->getRealPath());

        flashMessage('success', __('Flights imported'), __('Flights have been imported'));

        return to_route('events.bookings.index', $event);
    }
}

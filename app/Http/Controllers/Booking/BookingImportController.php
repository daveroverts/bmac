<?php

namespace App\Http\Controllers\Booking;

use App\Enums\EventType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Booking\Admin\ImportBookings;
use App\Imports\BookingsImport;
use App\Models\Event;
use App\Services\AirportImporter;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Facades\Excel;

class BookingImportController extends Controller
{
    public function create(Event $event): View
    {
        return view('event.admin.import', ['event' => $event]);
    }

    public function store(ImportBookings $request, Event $event, AirportImporter $airportImporter): RedirectResponse
    {
        activity()
            ->by(auth()->user())
            ->on($event)
            ->log('Import triggered');

        $file = $request->file('file');

        $airportImporter->ensure($this->referencedAirports($file, $event));

        (new BookingsImport($event))->import($file);
        Storage::delete($file->getRealPath());

        flashMessage('success', __('Flights imported'), __('Flights have been imported'));

        return to_route('events.bookings.index', $event);
    }

    /**
     * Collect the ICAO codes referenced by the uploaded file so missing airports
     * can be created before the import's airport validation runs.
     *
     * @return list<string>
     */
    private function referencedAirports(mixed $file, Event $event): array
    {
        $columns = $event->event_type_id == EventType::MULTIFLIGHTS->value
            ? ['airport_1', 'airport_2', 'airport_3']
            : ['origin', 'destination'];

        $rows = Excel::toArray(new class () implements WithHeadingRow {}, $file)[0] ?? [];

        return collect($rows)
            ->flatMap(fn (array $row): array => array_map(fn (string $column): mixed => $row[$column] ?? null, $columns))
            ->filter()
            ->values()
            ->all();
    }
}

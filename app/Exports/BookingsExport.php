<?php

namespace App\Exports;

use App\Enums\EventType;
use App\Models\Booking;
use App\Models\Event;
use Spatie\SimpleExcel\SimpleExcelWriter;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class BookingsExport
{
    public function __construct(public Event $event, public ?bool $vacc)
    {
    }

    public function download(string $fileName): BinaryFileResponse
    {
        $path = tempnam(sys_get_temp_dir(), 'bookings-export') . '.csv';

        $writer = SimpleExcelWriter::create($path)->noHeaderRow();

        $this->event->bookings()
            ->with(['user', 'flights.airportDep', 'flights.airportArr'])
            ->booked()
            ->get()
            ->each(fn (Booking $booking) => $writer->addRow($this->map($booking)));

        $writer->close();

        return response()->download($path, $fileName)->deleteFileAfterSend();
    }

    /**
     * @return list<mixed>
     */
    public function map(Booking $booking): array
    {
        if ($this->event->event_type_id == EventType::MULTIFLIGHTS->value) {
            $flight1 = $booking->flights->first();
            $flight2 = $booking->flights->where('id', '!=', $flight1->id)->first();
            if ($this->vacc === true) {
                return [
                    $booking->user->full_name,
                    $booking->user_id,
                    $booking->user->email,
                    $booking->callsign,
                    $flight1->airportDep->icao,
                    $flight2->airportDep->icao,
                    $flight2->airportArr->icao,
                ];
            }

            return [
                $booking->user->full_name,
                $booking->user_id,
                $booking->callsign,
                $flight1->airportDep->icao,
                $flight1->ctot?->format('H:i:s'),
                $flight2->airportDep->icao,
                $flight2->ctot?->format('H:i:s'),
                $flight2->airportArr->icao,
            ];
        }

        $flight = $booking->flights->first();
        return [
            $booking->user->full_name,
            $booking->user_id,
            $booking->callsign,
            $booking->acType,
            $flight->airportDep->icao,
            $flight->airportArr->icao,
            $flight->getRawOriginal('oceanicFL'),
            $flight->ctot?->format('H:i:s'),
            $flight->eta?->format('H:i:s'),
            $flight->route,
        ];
    }
}

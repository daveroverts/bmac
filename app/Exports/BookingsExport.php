<?php

namespace App\Exports;

use App\Models\Event;
use App\Enums\BookingStatus;
use App\Enums\EventType;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class BookingsExport implements FromCollection, WithColumnFormatting, WithMapping
{
    use Exportable;

    public function __construct(public Event $event, public ?bool $vacc)
    {
        $this->event = $event;
        $this->vacc = $vacc;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return $this->event->bookings()->with('flights')->whereStatus(BookingStatus::BOOKED)->get();
    }

    public function map($booking): array
    {
        if ($this->event->event_type_id == EventType::MULTIFLIGHTS) {
            $flight1 = $booking->flights()->first();
            $flight2 = $booking->flights()->whereKeyNot($flight1->id)->first();
            if ($this->vacc) {
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
                $flight1->ctot ? Date::dateTimeToExcel($flight1->ctot) : null,
                $flight2->airportDep->icao,
                $flight2->ctot ? Date::dateTimeToExcel($flight2->ctot) : null,
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
            $flight->ctot ? Date::dateTimeToExcel($flight->ctot) : null,
            $flight->eta ? Date::dateTimeToExcel($flight->eta) : null,
            $flight->route,
        ];
    }

    public function columnFormats(): array
    {
        if ($this->event->event_type_id == EventType::MULTIFLIGHTS && !$this->vacc) {
            return [
                'E' => NumberFormat::FORMAT_DATE_TIME4,
                'G' => NumberFormat::FORMAT_DATE_TIME4,
            ];
        }
        return [
            'H' => NumberFormat::FORMAT_DATE_TIME4,
            'I' => NumberFormat::FORMAT_DATE_TIME4,
        ];
    }
}

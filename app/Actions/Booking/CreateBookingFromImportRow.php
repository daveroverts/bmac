<?php

namespace App\Actions\Booking;

use App\Enums\EventType;
use App\Models\Airport;
use App\Models\Booking;
use App\Models\Event;
use DateTimeInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Date as DateFacade;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class CreateBookingFromImportRow
{
    /** @var Collection<string, int>|null */
    private ?Collection $airports = null;

    /**
     * Create a booking (and its flights) from a single parsed import row.
     *
     * @param  array<string, mixed>  $row
     */
    public function handle(Event $event, array $row): Booking
    {
        $booking = Booking::create([
            'event_id' => $event->id,
            'is_callsign_editable' => empty($row['call_sign']),
            'is_actype_editable' => empty($row['aircraft_type']),
            'callsign' => $row['call_sign'] ?? null,
            'acType' => $row['aircraft_type'] ?? null,
        ]);

        $airports = $this->airports();

        if ($event->event_type_id == EventType::MULTIFLIGHTS->value) {
            $booking->flights()->createMany([
                [
                    'order_by' => 1,
                    'dep' => $airports[$row['airport_1']],
                    'arr' => $airports[$row['airport_2']],
                    'ctot' => $this->getTime($event, $row['ctot_1'] ?? null),
                ],
                [
                    'order_by' => 2,
                    'dep' => $airports[$row['airport_2']],
                    'arr' => $airports[$row['airport_3']],
                    'ctot' => $this->getTime($event, $row['ctot_2'] ?? null),
                ],
            ]);

            return $booking;
        }

        $booking->flights()->create([
            'dep' => $airports[$row['origin']],
            'arr' => $airports[$row['destination']],
            'notes' => $row['notes'] ?? null,
            'ctot' => $this->getTime($event, $row['ctot'] ?? null),
            'eta' => $this->getTime($event, $row['eta'] ?? null),
            'oceanicTrack' => $row['track'] ?? null,
            'oceanicFL' => $row['fl'] ?? null,
            'route' => $row['route'] ?? null,
        ]);

        return $booking;
    }

    /**
     * @return Collection<string, int>
     */
    private function airports(): Collection
    {
        return $this->airports ??= Airport::pluck('id', 'icao');
    }

    private function getTime(Event $event, mixed $time): ?DateTimeInterface
    {
        if (empty($time)) {
            return null;
        }

        $dateTime = is_numeric($time)
            ? Date::excelToDateTimeObject($time)
            : DateFacade::createFromFormat('H:i', trim((string) $time));

        $dateTime->setDate(
            $event->startEvent->year,
            $event->startEvent->month,
            $event->startEvent->day,
        );

        return $dateTime;
    }
}

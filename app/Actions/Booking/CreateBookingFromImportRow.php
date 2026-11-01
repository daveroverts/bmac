<?php

namespace App\Actions\Booking;

use App\Enums\EventType;
use App\Models\Airport;
use App\Models\Booking;
use App\Models\Event;
use Carbon\CarbonInterface;
use DateTimeInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Date as DateFacade;

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
            $firstCtot = $this->resolveTime($event, $row['ctot_1'] ?? null);
            $secondCtot = $this->resolveTime($event, $row['ctot_2'] ?? null, $firstCtot);

            $booking->flights()->createMany([
                [
                    'order_by' => 1,
                    'dep' => $airports[$row['airport_1']],
                    'arr' => $airports[$row['airport_2']],
                    'ctot' => $firstCtot,
                ],
                [
                    'order_by' => 2,
                    'dep' => $airports[$row['airport_2']],
                    'arr' => $airports[$row['airport_3']],
                    'ctot' => $secondCtot,
                ],
            ]);

            return $booking;
        }

        $ctot = $this->resolveTime($event, $row['ctot'] ?? null);

        $booking->flights()->create([
            'dep' => $airports[$row['origin']],
            'arr' => $airports[$row['destination']],
            'notes' => $row['notes'] ?? null,
            'ctot' => $ctot,
            'eta' => $this->resolveTime($event, $row['eta'] ?? null, $ctot),
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

    /**
     * Place a time cell on the right date: nearest the event window, or at or after $after when given.
     */
    private function resolveTime(Event $event, mixed $time, ?CarbonInterface $after = null): ?Carbon
    {
        $formatted = $this->parseTime($time);

        if ($formatted === null) {
            return null;
        }

        return $after instanceof \Carbon\CarbonInterface
            ? $event->resolveTimeAfter($formatted, $after)
            : $event->resolveSlotTime($formatted);
    }

    /**
     * Normalize a DateTime, Excel serial or "H:i" cell to "H:i". Numeric 0 is a valid Excel midnight.
     */
    private function parseTime(mixed $time): ?string
    {
        if ($time === null || (is_string($time) && trim($time) === '')) {
            return null;
        }

        $dateTime = match (true) {
            $time instanceof DateTimeInterface => DateFacade::instance($time),
            is_numeric($time) => $this->excelTimeToDateTime((float) $time),
            default => DateFacade::createFromFormat('H:i', trim((string) $time)),
        };

        return $dateTime->format('H:i');
    }

    /**
     * Convert an Excel serial date (days since 1899-12-30) to a DateTime.
     */
    private function excelTimeToDateTime(float $excelTimestamp): Carbon
    {
        $unixTimestamp = (int) round(($excelTimestamp - 25569) * 86400);

        return DateFacade::createFromTimestampUTC($unixTimestamp);
    }
}

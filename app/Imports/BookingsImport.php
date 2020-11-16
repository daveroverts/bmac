<?php

namespace App\Imports;

use App\Enums\EventType;
use App\Models\Event;
use App\Models\Airport;
use App\Models\Booking;
use Maatwebsite\Excel\Concerns\ToModel;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class BookingsImport implements ToModel, WithHeadingRow, WithBatchInserts, WithChunkReading, WithValidation
{
    use Importable;

    public $event;

    public function __construct(Event $event)
    {
        $this->event = $event;
    }

    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        $editable = true;
        if (!empty($line['call_sign']) && !empty($line['aircraft_type'])) {
            $editable = false;
        }
        $booking = Booking::create([
            'event_id' => $this->event->id,
            'editable' => $editable,
            'callsign' => $row['call_sign'],
            'acType' => $row['aircraft_type'],
        ]);

        if ($this->event->event_type_id == EventType::MULTIFLIGHTS) {
            $airport1 = $this->getAirport($row['airport_1']);
            $airport2 = $this->getAirport($row['airport_2']);
            $airport3 = $this->getAirport($row['airport_3']);
            $ctot1 = $this->getTime($row['ctot_1'] ?? null);
            $ctot2 = $this->getTime($row['ctot_2'] ?? null);

            $booking->flights()->createMany([
                [
                    'order_by' => 1,
                    'dep' => $airport1,
                    'arr' => $airport2,
                    'ctot' => $ctot1,
                ],
                [
                    'order_by' => 2,
                    'dep' => $airport2,
                    'arr' => $airport3,
                    'ctot' => $ctot2,
                ],
            ]);
        } else {
            $flight = collect([
                'dep'   => $this->getAirport($row['origin']),
                'arr'   => $this->getAirport($row['destination']),
                'notes' => $row['notes'],
                'ctot'  => $this->getTime($row['ctot'] ?? null),
                'eta'  => $this->getTime($row['eta'] ?? null),
            ]);
            $booking->flights()->create($flight->toArray());
        }
    }

    public function batchSize(): int
    {
        return 250;
    }

    public function chunkSize(): int
    {
        return 250;
    }

    public function rules(): array
    {
        if ($this->event->event_type_id == EventType::MULTIFLIGHTS) {
            return [
                'airport_1'      => 'exists:airports,icao',
                'airport_2' => 'exists:airports,icao',
                'airport_3' => 'exists:airports,icao',
            ];
        }
        return [
            'origin'      => 'exists:airports,icao',
            'destination' => 'exists:airports,icao',
        ];
    }

    private function getAirport($icao): int
    {
        return Airport::whereIcao($icao)->first()->id;
    }

    private function getTime($time)
    {
        if (!empty($time)) {
            $time = Date::excelToDateTimeObject($time);
            $time->setDate(
                $this->event->startEvent->year,
                $this->event->startEvent->month,
                $this->event->startEvent->day,
            );
            return $time;
        }
        return null;
    }
}

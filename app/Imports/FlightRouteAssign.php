<?php

namespace App\Imports;

use App\Models\Flight;
use App\Models\Airport;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Importable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class FlightRouteAssign implements ToCollection, WithHeadingRow, WithBatchInserts, WithChunkReading, WithValidation, ShouldQueue, SkipsEmptyRows
{
    use Importable;

    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            $from = $this->getAirport($row['from']);
            $to =  $this->getAirport($row['to']);
            Flight::whereDep($from)
                ->whereArr($to)
                ->update([
                    'route' => $row['route'],
                    'notes' => $row['notes'] ?? null
                ]);
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
        return [
            'from'  => 'exists:airports,icao',
            'to'    => 'exists:airports,icao',
            'route' => 'nullable',
            'notes' => 'nullable',
        ];
    }

    private function getAirport($icao): int
    {
        return Airport::whereIcao($icao)->first()->id;
    }
}

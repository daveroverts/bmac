<?php

namespace App\Imports;

use App\Models\Flight;
use App\Models\Airport;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class FlightRouteAssign implements ToCollection, WithHeadingRow, WithValidation
{
    use Importable;

    public function collection(Collection $rows): void
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

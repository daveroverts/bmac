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

    /** @var Collection<string, int> */
    private Collection $airports;

    public function collection(Collection $rows): void
    {
        $this->airports = Airport::pluck('id', 'icao');

        foreach ($rows as $row) {
            $from = $this->airports[$row['from']];
            $to = $this->airports[$row['to']];
            Flight::whereDep($from)
                ->whereArr($to)
                ->update([
                    'route' => $row['route'],
                    'notes' => $row['notes'] ?? null,
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
}

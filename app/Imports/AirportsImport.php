<?php

namespace App\Imports;

use App\Models\Airport;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithUpserts;
use Maatwebsite\Excel\Concerns\WithValidation;

class AirportsImport implements ShouldQueue, ToModel, WithBatchInserts, WithChunkReading, WithHeadingRow, WithUpserts, WithValidation, SkipsOnFailure
{
    use Importable;
    use SkipsFailures;

    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        return new Airport([
            'icao' => $row['icao'],
            'iata' => $row['iata'],
            'name' => $row['name'],
            'latitude' => $row['lat'],
            'longitude' => $row['lon'],
        ]);
    }

    public function rules(): array
    {
        return [
            'icao' => ['required', 'string', Rule::unique('airports', 'icao')],
            'iata' => ['required', 'string', Rule::unique('airports', 'iata')],
            'name' => ['required', 'string'],
            'lat' => ['required', 'regex:/^(\+|-)?(?:90(?:(?:\.0{1,6})?)|(?:[0-9]|[1-8][0-9])(?:(?:\.[0-9]{1,6})?))$/'],
            'lon' => ['required', 'regex:/^(\+|-)?(?:180(?:(?:\.0{1,6})?)|(?:[0-9]|[1-9][0-9]|1[0-7][0-9])(?:(?:\.[0-9]{1,6})?))$/'],
        ];
    }

    public function uniqueBy()
    {
        return 'icao';
    }

    public function batchSize(): int
    {
        return 1000;
    }

    public function chunkSize(): int
    {
        return 1000;
    }
}

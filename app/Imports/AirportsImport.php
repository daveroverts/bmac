<?php

namespace App\Imports;

use App\Models\Airport;
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

class AirportsImport implements ToModel, WithBatchInserts, WithChunkReading, WithHeadingRow, WithUpserts, WithValidation, SkipsOnFailure
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
        ]);
    }

    public function rules(): array
    {
        return [
            'icao' => ['required', 'string', Rule::unique('airports', 'icao')],
            'iata' => ['required', 'string', Rule::unique('airports', 'iata')],
            'name' => ['required', 'string'],
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

<?php

namespace App\Imports;

use App\Models\Airport;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithUpserts;

class AirportsImport implements ToModel, WithBatchInserts, WithChunkReading, WithHeadingRow, WithUpserts
{
    use Importable;

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

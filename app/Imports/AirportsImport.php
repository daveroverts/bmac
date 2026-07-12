<?php

declare(strict_types=1);

namespace App\Imports;

use App\Models\Airport;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\LazyCollection;
use Spatie\SimpleExcel\SimpleExcelReader;

class AirportsImport
{
    private const int CHUNK_SIZE = 1000;

    public function import(string $path, string $disk = 'local'): void
    {
        $existingIcaos = Airport::pluck('icao')->flip();
        $existingIatas = Airport::pluck('iata')->flip();

        SimpleExcelReader::create(Storage::disk($disk)->path($path), 'csv')
            ->getRows()
            ->filter(fn (array $row): bool => $this->isValid($row)
                && ! $existingIcaos->has($row['icao'])
                && ! $existingIatas->has($row['iata']))
            ->map(fn (array $row): array => [
                'icao' => $row['icao'],
                'iata' => $row['iata'],
                'name' => $row['name'],
                'latitude' => $row['lat'],
                'longitude' => $row['lon'],
            ])
            ->chunk(self::CHUNK_SIZE)
            ->each(fn (LazyCollection $airports) => Airport::upsert($airports->values()->all(), ['icao']));
    }

    /**
     * @param  array<string, mixed>  $row
     */
    private function isValid(array $row): bool
    {
        return Validator::make($row, [
            'icao' => ['required', 'string'],
            'iata' => ['required', 'string'],
            'name' => ['required', 'string'],
            'lat' => ['required', 'regex:/^[-]?((([0-8]?[0-9])(\.(\d{1,10}))?)|(90(\.0+)?))$/'],
            'lon' => ['required', 'regex:/^[-]?((((1[0-7][0-9])|([0-9]?[0-9]))(\.(\d{1,10}))?)|180(\.0+)?)/'],
        ])->passes();
    }
}

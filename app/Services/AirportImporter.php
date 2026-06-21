<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Airport;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class AirportImporter
{
    /**
     * Source dataset of worldwide airports keyed by ICAO.
     */
    public const string SOURCE_URL = 'https://raw.githubusercontent.com/mborsetti/airportsdata/main/airportsdata/airports.csv';

    private const string CACHE_KEY = 'airportsdata.csv';

    /**
     * Ensure the given ICAO codes exist as Airports, creating any missing ones
     * from the airportsdata dataset on demand.
     *
     * @param  iterable<int, string|null>  $icaoCodes
     * @return array{created: list<string>, unresolved: list<string>}  unresolved = not found in the dataset (or lacking a required IATA code)
     */
    public function ensure(iterable $icaoCodes): array
    {
        $icaos = collect($icaoCodes)
            ->map(fn (mixed $icao): string => strtoupper(trim((string) $icao)))
            ->filter()
            ->unique()
            ->values();

        if ($icaos->isEmpty()) {
            return ['created' => [], 'unresolved' => []];
        }

        $existing = Airport::whereIn('icao', $icaos)->pluck('icao');
        $missing = $icaos->diff($existing)->values();

        if ($missing->isEmpty()) {
            return ['created' => [], 'unresolved' => []];
        }

        $dataset = $this->dataset();

        $created = [];
        $unresolved = [];

        foreach ($missing as $icao) {
            $data = $dataset->get($icao);

            // `iata` is NOT NULL UNIQUE, so dataset rows without one cannot be created
            // safely (empty strings would collide). Treat those as unresolved.
            if ($data === null || $data['iata'] === '') {
                $unresolved[] = $icao;

                continue;
            }

            Airport::create($data);
            $created[] = $icao;
        }

        return ['created' => $created, 'unresolved' => $unresolved];
    }

    /**
     * The dataset keyed by ICAO, cached to avoid re-downloading on every import.
     *
     * @return Collection<string, array{icao: string, iata: string, name: string, latitude: string, longitude: string}>
     */
    private function dataset(): Collection
    {
        $csv = Cache::remember(
            self::CACHE_KEY,
            now()->addDay(),
            fn (): string => Http::get(self::SOURCE_URL)->body(),
        );

        return $this->parse($csv);
    }

    /**
     * @return Collection<string, array{icao: string, iata: string, name: string, latitude: string, longitude: string}>
     */
    private function parse(string $csv): Collection
    {
        $lines = preg_split('/\r\n|\r|\n/', trim($csv)) ?: [];
        $header = str_getcsv((string) array_shift($lines));
        $column = array_flip($header);

        return collect($lines)
            ->filter(fn (string $line): bool => trim($line) !== '')
            ->mapWithKeys(function (string $line) use ($column): array {
                $cols = str_getcsv($line);
                $icao = strtoupper($cols[$column['icao']] ?? '');

                return [$icao => [
                    'icao' => $icao,
                    'iata' => $cols[$column['iata']] ?? '',
                    'name' => $cols[$column['name']] ?? '',
                    'latitude' => $cols[$column['lat']] ?? '',
                    'longitude' => $cols[$column['lon']] ?? '',
                ]];
            });
    }
}

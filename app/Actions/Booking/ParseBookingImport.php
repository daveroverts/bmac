<?php

namespace App\Actions\Booking;

use App\Enums\EventType;
use App\Models\Event;
use App\Services\AirportImporter;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Spatie\SimpleExcel\SimpleExcelReader;

class ParseBookingImport
{
    public function __construct(private readonly AirportImporter $airportImporter)
    {
    }

    /**
     * Read an import file, create any missing airports, then validate each row.
     *
     * @return array{rows: list<array{data: array<string, mixed>, errors: list<string>, valid: bool}>, autoAddedAirports: list<string>}
     */
    public function handle(Event $event, string $path, string $extension): array
    {
        $sheet = SimpleExcelReader::create($path, $extension)
            ->formatHeadersUsing(fn (string $header): string => Str::slug($header, '_'))
            ->getRows()
            ->map(fn (array $row): array => array_map(
                fn (mixed $value): mixed => $value === '' ? null : $value,
                $row
            ))
            ->values()
            ->all();

        $autoAddedAirports = $this->airportImporter->ensure($this->referencedIcaos($event, $sheet))['created'];

        $rules = $this->rowRules($event);
        $rows = [];

        foreach ($sheet as $row) {
            $errors = Validator::make($row, $rules)->errors()->all();

            $rows[] = [
                'data' => $row,
                'errors' => $errors,
                'valid' => $errors === [],
            ];
        }

        return ['rows' => $rows, 'autoAddedAirports' => $autoAddedAirports];
    }

    /**
     * @param  array<int, array<string, mixed>>  $sheet
     * @return list<string>
     */
    private function referencedIcaos(Event $event, array $sheet): array
    {
        $columns = $event->event_type_id == EventType::MULTIFLIGHTS->value
            ? ['airport_1', 'airport_2', 'airport_3']
            : ['origin', 'destination'];

        return collect($sheet)
            ->flatMap(fn (array $row): array => array_map(fn (string $column): mixed => $row[$column] ?? null, $columns))
            ->filter()
            ->values()
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    private function rowRules(Event $event): array
    {
        if ($event->event_type_id == EventType::MULTIFLIGHTS->value) {
            return [
                'airport_1' => 'exists:airports,icao',
                'airport_2' => 'exists:airports,icao',
                'airport_3' => 'exists:airports,icao',
            ];
        }

        return [
            'origin' => 'exists:airports,icao',
            'destination' => 'exists:airports,icao',
            'track' => 'sometimes|nullable',
            'oceanicFL' => 'sometimes|nullable|integer:3',
            'aircraft_type' => 'sometimes|nullable|max:4',
        ];
    }
}

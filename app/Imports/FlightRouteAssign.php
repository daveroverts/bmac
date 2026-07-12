<?php

namespace App\Imports;

use App\Models\Flight;
use App\Models\Airport;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Spatie\SimpleExcel\SimpleExcelReader;

class FlightRouteAssign
{
    /** @var Collection<string, int> */
    private Collection $airports;

    public function import(UploadedFile $file): void
    {
        $rows = SimpleExcelReader::create($file->getRealPath(), $file->getClientOriginalExtension())
            ->formatHeadersUsing(fn (string $header): string => Str::slug($header, '_'))
            ->getRows()
            ->map(fn (array $row): array => array_map(
                fn (mixed $value): mixed => $value === '' ? null : $value,
                $row
            ))
            ->values()
            ->all();

        $this->validate($rows);

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

    /**
     * @param  array<int, array<mixed>>  $rows
     */
    private function validate(array $rows): void
    {
        $errors = [];

        foreach ($rows as $index => $row) {
            $validator = Validator::make($row, $this->rules());

            foreach ($validator->errors()->messages() as $attribute => $messages) {
                $errors["{$index}.{$attribute}"] = $messages;
            }
        }

        if ($errors !== []) {
            throw ValidationException::withMessages($errors);
        }
    }

    /**
     * @return array<string, mixed>
     */
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

<?php

namespace App\Livewire\Booking\Admin;

use App\Actions\Booking\CreateBookingFromImportRow;
use App\Enums\EventType;
use App\Models\Event;
use App\Services\AirportImporter;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Validator;
use Livewire\Component;
use Livewire\WithFileUploads;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Facades\Excel;

class Import extends Component
{
    use WithFileUploads;

    public Event $event;

    public $file;

    /** @var array<int, array{data: array<string, mixed>, errors: list<string>, valid: bool}> */
    public array $rows = [];

    /** @var list<int> */
    public array $selected = [];

    /** @var list<string> */
    public array $autoAddedAirports = [];

    public function mount(Event $event): void
    {
        abort_unless(auth()->user()?->isAdmin, 404);

        $this->event = $event;
    }

    public function updatedFile(): void
    {
        $this->validate([
            'file' => ['required', 'file', 'mimes:csv,xlsx,xls', 'max:10240'],
        ]);

        $this->parse();
    }

    public function selectAll(): void
    {
        $this->selected = collect($this->rows)
            ->filter(fn (array $row): bool => $row['valid'])
            ->keys()
            ->all();
    }

    public function selectNone(): void
    {
        $this->selected = [];
    }

    public function import(CreateBookingFromImportRow $createBooking): void
    {
        abort_unless(auth()->user()?->isAdmin, 404);

        activity()
            ->by(auth()->user())
            ->on($this->event)
            ->log('Import triggered');

        $selected = array_map(intval(...), $this->selected);

        foreach ($this->rows as $index => $row) {
            if (! $row['valid']) {
                continue;
            }
            if (! in_array($index, $selected, true)) {
                continue;
            }
            $createBooking->handle($this->event, $row['data']);
        }

        flashMessage('success', __('Flights imported'), __('Flights have been imported'));

        $this->redirectRoute('events.bookings.index', $this->event);
    }

    public function render(): View
    {
        return view('livewire.booking.admin.import');
    }

    /**
     * Read the uploaded file, create any missing airports, then validate each row.
     */
    private function parse(): void
    {
        $sheet = Excel::toArray(new class () implements WithHeadingRow {}, $this->file)[0] ?? [];

        $this->autoAddedAirports = resolve(AirportImporter::class)
            ->ensure($this->referencedIcaos($sheet))['created'];

        $rules = $this->rowRules();

        $this->rows = [];
        $this->selected = [];

        foreach ($sheet as $row) {
            $errors = Validator::make($row, $rules)->errors()->all();
            $valid = $errors === [];
            $index = count($this->rows);

            $this->rows[] = [
                'data' => $row,
                'errors' => $errors,
                'valid' => $valid,
            ];

            if ($valid) {
                $this->selected[] = $index;
            }
        }
    }

    /**
     * @param  array<int, array<string, mixed>>  $sheet
     * @return list<string>
     */
    private function referencedIcaos(array $sheet): array
    {
        $columns = $this->event->event_type_id == EventType::MULTIFLIGHTS->value
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
    private function rowRules(): array
    {
        if ($this->event->event_type_id == EventType::MULTIFLIGHTS->value) {
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

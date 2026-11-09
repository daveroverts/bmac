<?php

namespace App\Livewire\Booking\Admin;

use App\Actions\Booking\CreateBookingFromImportRow;
use App\Actions\Booking\ParseBookingImport;
use App\Models\Event;
use Illuminate\Contracts\View\View;
use Livewire\Component;
use Livewire\WithFileUploads;

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
            'file' => ['required', 'file', 'mimes:csv,xlsx', 'max:10240'],
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
        $result = resolve(ParseBookingImport::class)->handle(
            $this->event,
            $this->file->getRealPath(),
            $this->file->getClientOriginalExtension(),
        );

        $this->autoAddedAirports = $result['autoAddedAirports'];
        $this->rows = $result['rows'];
        $this->selected = array_keys(array_filter($this->rows, fn (array $row): bool => $row['valid']));
    }
}

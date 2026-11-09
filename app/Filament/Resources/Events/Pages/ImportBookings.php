<?php

namespace App\Filament\Resources\Events\Pages;

use App\Actions\Booking\CreateBookingFromImportRow;
use App\Actions\Booking\ParseBookingImport;
use App\Enums\EventType;
use App\Filament\Resources\Events\EventResource;
use App\Models\Event;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\EmbeddedTable;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Text;
use Filament\Schemas\Schema;
use Filament\Support\ArrayRecord;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

/**
 * Upload a CSV/XLSX file, preview every row with its validation errors, then import the selected rows.
 *
 * @property-read Schema $form
 */
class ImportBookings extends Page implements HasTable
{
    use InteractsWithRecord;
    use InteractsWithTable;

    protected static string $resource = EventResource::class;

    /** @var array<string, mixed>|null */
    public ?array $data = [];

    /** @var list<array{data: array<string, mixed>, errors: list<string>, valid: bool}> */
    public array $rows = [];

    /** @var list<string> */
    public array $autoAddedAirports = [];

    public function mount(int|string $record): void
    {
        $this->record = $this->resolveRecord($record);

        abort_unless(static::getResource()::canEdit($this->getRecord()), 403);

        $this->form->fill();
    }

    public function getTitle(): string
    {
        return __('Import bookings: :event', ['event' => $this->getEvent()->name]);
    }

    public function getEvent(): Event
    {
        /** @var Event $event */
        $event = $this->getRecord();

        return $event;
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->components([
                FileUpload::make('file')
                    ->label(__('File'))
                    ->helperText($this->isMultiFlights()
                        ? __('CSV or XLSX, max 10 MB. Columns: Airport 1, Airport 2, Airport 3, CTOT 1, CTOT 2, Call sign, Aircraft type.')
                        : __('CSV or XLSX, max 10 MB. Columns: Origin, Destination, CTOT, ETA, Call sign, Aircraft type, Route, Track, FL, Notes.'))
                    ->storeFiles(false)
                    ->maxSize(10240)
                    ->live()
                    ->afterStateUpdated(fn (mixed $state) => $this->parse($state)),
            ]);
    }

    public function content(Schema $schema): Schema
    {
        return $schema->components([
            Section::make()->schema([EmbeddedSchema::make('form')]),
            Text::make(fn (): string => __('Automatically added :count missing airport(s): :list', [
                'count' => count($this->autoAddedAirports),
                'list' => implode(', ', $this->autoAddedAirports),
            ]))
                ->color('info')
                ->visible(fn (): bool => $this->autoAddedAirports !== []),
            EmbeddedTable::make()->visible(fn (): bool => $this->rows !== []),
        ]);
    }

    public function table(Table $table): Table
    {
        $isMultiFlights = $this->isMultiFlights();

        $column = fn (string $key, string $label): TextColumn => TextColumn::make($key)
            ->label($label)
            ->state(fn (array $record): mixed => $record['data'][$key] ?? null)
            ->placeholder('-');

        return $table
            ->records(fn (): array => $this->tableRecords())
            ->paginated(false)
            ->heading(fn (): string => __(':valid of :total rows are valid', [
                'valid' => count(array_filter($this->rows, fn (array $row): bool => $row['valid'])),
                'total' => count($this->rows),
            ]))
            ->columns([
                TextColumn::make('row')->label('#')->state(fn (array $record): int => (int) $record[ArrayRecord::getKeyName()] + 1),
                ...($isMultiFlights
                    ? [$column('airport_1', __('Airport 1')), $column('airport_2', __('Airport 2')), $column('airport_3', __('Airport 3')), $column('ctot_1', __('CTOT 1')), $column('ctot_2', __('CTOT 2'))]
                    : [$column('origin', __('Origin')), $column('destination', __('Destination')), $column('ctot', __('CTOT')), $column('eta', __('ETA')), $column('route', __('Route'))->limit(30)]),
                $column('call_sign', __('Call sign')),
                $column('aircraft_type', __('Aircraft')),
                TextColumn::make('errors')
                    ->label(__('Issues'))
                    ->state(fn (array $record): array => $record['errors'] ?: [__('OK')])
                    ->badge()
                    ->color(fn (array $record): string => $record['valid'] ? 'success' : 'danger'),
            ])
            ->checkIfRecordIsSelectableUsing(fn (array $record): bool => $record['valid'])
            ->headerActions([
                Action::make('importAll')
                    ->label(__('Import all valid rows'))
                    ->icon(Heroicon::OutlinedArrowUpTray)
                    ->requiresConfirmation()
                    ->action(fn (CreateBookingFromImportRow $createBooking) => $this->import(
                        collect($this->tableRecords())->where('valid', true),
                        $createBooking,
                    )),
            ])
            ->toolbarActions([
                BulkAction::make('importSelected')
                    ->label(__('Import selected'))
                    ->icon(Heroicon::OutlinedArrowUpTray)
                    ->action(fn (Collection $records, CreateBookingFromImportRow $createBooking) => $this->import($records, $createBooking)),
            ]);
    }

    /**
     * @param  mixed  $state  The FileUpload state: a temporary upload, or an array of them keyed by id.
     */
    public function parse(mixed $state): void
    {
        $this->rows = [];
        $this->autoAddedAirports = [];

        $file = is_array($state) ? Arr::first($state) : $state;

        if (! $file instanceof TemporaryUploadedFile) {
            return;
        }

        $extension = strtolower($file->getClientOriginalExtension());

        if (! in_array($extension, ['csv', 'xlsx'], true)) {
            Notification::make()->title(__('Only CSV and XLSX files can be imported.'))->danger()->send();

            return;
        }

        $result = resolve(ParseBookingImport::class)->handle($this->getEvent(), $file->getRealPath(), $extension);

        $this->rows = $result['rows'];
        $this->autoAddedAirports = $result['autoAddedAirports'];
    }

    /**
     * @param  Collection<array-key, array<string, mixed>>  $records
     */
    private function import(Collection $records, CreateBookingFromImportRow $createBooking): void
    {
        activity()
            ->by(auth()->user())
            ->on($this->getEvent())
            ->log('Import triggered');

        $count = 0;

        foreach ($records as $record) {
            if (! $record['valid']) {
                continue;
            }

            $createBooking->handle($this->getEvent(), $record['data']);
            $count++;
        }

        Notification::make()
            ->title(trans_choice('{1} :count flight has been imported|[2,*] :count flights have been imported', $count, ['count' => $count]))
            ->success()
            ->send();

        $this->redirect(EventResource::getUrl('bookings', ['record' => $this->getEvent()]));
    }

    /**
     * @return array<int|string, array<string, mixed>>
     */
    private function tableRecords(): array
    {
        $records = [];

        foreach ($this->rows as $index => $row) {
            $records[(string) $index] = [ArrayRecord::getKeyName() => (string) $index, ...$row];
        }

        return $records;
    }

    private function isMultiFlights(): bool
    {
        return (int) $this->getEvent()->event_type_id === EventType::MULTIFLIGHTS->value;
    }
}

<?php

namespace App\Filament\Resources\Events\Actions;

use App\Actions\Booking\AutoAssignFlightsAction;
use App\Actions\Booking\CreateBookingsAction;
use App\Actions\Event\SendFinalInformationAction;
use App\Enums\EventType;
use App\Events\EventBulkEmail;
use App\Events\EventFinalInformation;
use App\Filament\Resources\Events\EventResource;
use App\Http\Requests\Booking\Admin\StoreBooking;
use App\Imports\FlightRouteAssign;
use App\Models\Airport;
use App\Models\Event;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Grid;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

/**
 * The event-level admin actions, shared by the events table, the event page and the event's bookings page.
 */
class EventActions
{
    /**
     * The event an action runs for: the table row, or the record of the page the action sits on.
     */
    public static function event(mixed $record, Component $livewire): Event
    {
        if ($record instanceof Event) {
            return $record;
        }

        /** @var Event $event */
        $event = $livewire->getRecord(); // @phpstan-ignore method.notFound

        return $event;
    }

    /**
     * @return array<int, Action|ActionGroup>
     */
    public static function all(): array
    {
        return [
            self::manageBookings(),
            self::addTimeslots(),
            self::importBookings(),
            self::autoAssign(),
            self::routeAssign(),
            self::sendBulkEmail(),
            self::sendFinalInformation(),
            self::export(),
            self::deleteAllBookings(),
        ];
    }

    public static function manageBookings(): Action
    {
        return Action::make('manageBookings')
            ->label(__('Manage bookings'))
            ->icon(Heroicon::OutlinedQueueList)
            ->url(fn (mixed $record, Component $livewire): string => EventResource::getUrl('bookings', ['record' => self::event($record, $livewire)]));
    }

    public static function importBookings(): Action
    {
        return Action::make('importBookings')
            ->label(__('Import data'))
            ->icon(Heroicon::OutlinedArrowUpTray)
            ->visible(fn (mixed $record, Component $livewire): bool => self::event($record, $livewire)->endEvent > now())
            ->url(fn (mixed $record, Component $livewire): string => EventResource::getUrl('import', ['record' => self::event($record, $livewire)]));
    }

    /**
     * Generate empty, time-separated slots between a start and end time.
     */
    public static function addTimeslots(): Action
    {
        return Action::make('addTimeslots')
            ->label(__('Add timeslots'))
            ->icon(Heroicon::OutlinedClock)
            ->modalDescription(__('Creates an empty slot every N minutes. A slot that already exists at the same time and departure airport is skipped.'))
            ->visible(fn (mixed $record, Component $livewire): bool => self::canAddManualSlots(self::event($record, $livewire)))
            ->fillForm(fn (mixed $record, Component $livewire): array => [
                'dep' => self::event($record, $livewire)->dep,
                'arr' => self::event($record, $livewire)->arr,
                'start' => self::event($record, $livewire)->startEvent->format('H:i'),
                'end' => self::event($record, $livewire)->endEvent->format('H:i'),
                'is_callsign_editable' => true,
                'is_actype_editable' => true,
            ])
            ->schema([
                Grid::make(2)->schema([
                    self::airportSelect('dep', __('Departure airport'))->required(),
                    self::airportSelect('arr', __('Arrival airport'))->required(),
                    self::time('start', __('Start'))->required()->helperText(__('If the end is before the start, the slots run past midnight.')),
                    self::time('end', __('End'))->required(),
                    TextInput::make('separation')
                        ->label(__('Separation (in minutes)'))
                        ->numeric()
                        ->minValue(1)
                        ->required(),
                ]),
                Grid::make(2)->schema([
                    Toggle::make('is_callsign_editable')->label(__('Callsign editable?')),
                    Toggle::make('is_actype_editable')->label(__('Aircraft code editable?')),
                ]),
                Textarea::make('notes')->label(__('Notes')),
            ])
            ->action(function (array $data, mixed $record, Component $livewire, CreateBookingsAction $createBookings): void {
                $event = self::event($record, $livewire);
                $count = $createBookings->handle(self::storeBookingRequest($event, [...$data, 'bulk' => true]), $event);

                Notification::make()
                    ->title(__(':count slots have been created!', ['count' => $count]))
                    ->success()
                    ->send();
            });
    }

    public static function autoAssign(): Action
    {
        return Action::make('autoAssign')
            ->label(__('Auto-assign FL / route'))
            ->icon(Heroicon::OutlinedArrowsRightLeft)
            ->modalDescription(__('Alternates two tracks over the flights in CTOT order and spreads the oceanic entry levels between the minimum and maximum.'))
            ->visible(fn (mixed $record, Component $livewire): bool => self::event($record, $livewire)->is_oceanic_event && self::event($record, $livewire)->endEvent > now())
            ->fillForm(['minFL' => 320, 'maxFL' => 380])
            ->schema([
                Grid::make(2)->schema([
                    TextInput::make('oceanicTrack1')->label(__('Track #:number', ['number' => 1]))->required()->alpha()->maxLength(2),
                    TextInput::make('oceanicTrack2')->label(__('Track #:number', ['number' => 2]))->required()->alpha()->maxLength(2),
                    Textarea::make('route1')->label(__('Route #:number', ['number' => 1]))->required(),
                    Textarea::make('route2')->label(__('Route #:number', ['number' => 2]))->required(),
                    TextInput::make('minFL')->label(__('Minimum Oceanic Entry FL'))->required()->integer()->prefix('FL'),
                    TextInput::make('maxFL')->label(__('Maximum Oceanic Entry FL'))->required()->integer()->prefix('FL')->gte('minFL'),
                ]),
                Toggle::make('checkAssignAllFlights')
                    ->label(__('Auto-assign all flights?'))
                    ->helperText(__('Off: only booked flights. On: also open and reserved slots.')),
            ])
            ->action(function (array $data, mixed $record, Component $livewire, AutoAssignFlightsAction $autoAssign): void {
                $count = $autoAssign->handle(self::event($record, $livewire), $data);

                Notification::make()
                    ->title(__(':count bookings have been Auto-Assigned a FL, and route', ['count' => $count]))
                    ->success()
                    ->send();
            });
    }

    public static function routeAssign(): Action
    {
        return Action::make('routeAssign')
            ->label(__('Assign routes'))
            ->icon(Heroicon::OutlinedMap)
            ->modalDescription(__('Upload a CSV or XLSX file with the columns From, To, Route and Notes. Every flight of this event between that pair gets the route and notes.'))
            ->visible(fn (mixed $record, Component $livewire): bool => self::event($record, $livewire)->event_type_id == EventType::MULTIFLIGHTS->value)
            ->schema([
                FileUpload::make('file')
                    ->label(__('File'))
                    ->required()
                    ->storeFiles(false)
                    ->rules(['mimes:csv,xlsx', 'max:10240']),
            ])
            ->action(function (array $data, mixed $record, Component $livewire, Action $action): void {
                $event = self::event($record, $livewire);

                /** @var UploadedFile $file */
                $file = $data['file'];

                activity()
                    ->by(auth()->user())
                    ->on($event)
                    ->log('Route assign triggered');

                try {
                    (new FlightRouteAssign($event))->import($file);
                } catch (ValidationException $validationException) {
                    Notification::make()
                        ->title(__('The file has errors, nothing was changed'))
                        ->body(collect($validationException->errors())->map(fn (array $messages, string $key): string => sprintf('Row %d: %s', ((int) $key) + 1, implode(' ', $messages)))->take(10)->implode("\n"))
                        ->danger()
                        ->persistent()
                        ->send();

                    $action->halt();
                }

                Notification::make()
                    ->title(__('Routes have been assigned to flights'))
                    ->success()
                    ->send();
            });
    }

    public static function sendBulkEmail(): Action
    {
        return Action::make('sendBulkEmail')
            ->label(__('Send mail to all'))
            ->icon(Heroicon::OutlinedEnvelope)
            ->modalHeading(__('Send e-mail to all booked pilots'))
            ->modalSubmitActionLabel(__('Send'))
            ->schema([
                TextInput::make('subject')->label(__('Subject'))->required(),
                RichEditor::make('message')
                    ->label(__('Message'))
                    ->required()
                    ->helperText(__('Salutation and closing are already included')),
                Toggle::make('testmode')
                    ->label(__('Test mode: only send it to myself'))
                    ->helperText(__('The form stays open, so you can send it for real afterwards.')),
            ])
            ->action(function (array $data, mixed $record, Component $livewire, Action $action): void {
                $event = self::event($record, $livewire);
                $request = ['subject' => $data['subject'], 'message' => $data['message']];

                if ($data['testmode'] ?? false) {
                    event(new EventBulkEmail($event, [...$request, 'testmode' => true], User::query()->whereKey(auth()->id())->get()));

                    Notification::make()->title(__('Email has been sent to yourself'))->success()->send();

                    $action->halt();
                }

                $users = User::whereHas('bookings', function (Builder $query) use ($event): void {
                    $query->where('event_id', $event->id)->booked();
                })->get();

                event(new EventBulkEmail($event, $request, $users));

                Notification::make()
                    ->title(__('Bulk E-mail has been sent to :count people!', ['count' => $users->count()]))
                    ->success()
                    ->send();
            });
    }

    public static function sendFinalInformation(): Action
    {
        return Action::make('sendFinalInformation')
            ->label(__('Send final information'))
            ->icon(Heroicon::OutlinedPaperAirplane)
            ->modalHeading(__('Send the final information e-mail'))
            ->modalDescription(__('Every booked pilot gets the final information for their flight. Pilots who already received it, and whose booking was not changed since, are skipped.'))
            ->modalSubmitActionLabel(__('Send'))
            ->schema([
                Toggle::make('forceSend')
                    ->label(__('Send to everybody'))
                    ->helperText(__('Also send it to pilots who already received it.')),
                Toggle::make('testmode')
                    ->label(__('Test mode: send a random one to myself')),
            ])
            ->action(function (array $data, mixed $record, Component $livewire, Action $action, SendFinalInformationAction $sendFinalInformation): void {
                $event = self::event($record, $livewire);

                if ($data['testmode'] ?? false) {
                    $booking = $event->bookings()->with(['user', 'flights'])->booked()->inRandomOrder()->first();

                    if (! $booking) {
                        Notification::make()->title(__('No booked bookings found for this event'))->danger()->send();
                    } else {
                        event(new EventFinalInformation($booking, auth()->user()));
                        Notification::make()->title(__('Email has been sent to yourself'))->success()->send();
                    }

                    $action->halt();
                }

                ['sent' => $sent, 'skipped' => $skipped] = $sendFinalInformation->handle($event, (bool) ($data['forceSend'] ?? false));

                Notification::make()
                    ->title(__('Final Information has been sent to :count people!', ['count' => $sent]))
                    ->body($skipped !== 0 ? __('However, :count were skipped, because they already received one', ['count' => $skipped]) : null)
                    ->success()
                    ->send();
            });
    }

    public static function export(): ActionGroup
    {
        return ActionGroup::make([
            Action::make('exportBookings')
                ->label(__('Bookings (CSV)'))
                ->url(fn (mixed $record, Component $livewire): string => route('admin.events.bookings.export', self::event($record, $livewire))),
            Action::make('exportVatcanCsv')
                ->label(__('VATCAN slots (CSV)'))
                ->url(fn (mixed $record, Component $livewire): string => route('admin.events.bookings.export', [self::event($record, $livewire), 'format' => 'vatcan-csv'])),
            Action::make('exportVatcanJson')
                ->label(__('VATCAN slots (JSON)'))
                ->url(fn (mixed $record, Component $livewire): string => route('admin.events.bookings.export', [self::event($record, $livewire), 'format' => 'vatcan-json'])),
        ])
            ->label(__('Export'))
            ->icon(Heroicon::OutlinedArrowDownTray)
            ->button()
            ->color('gray');
    }

    public static function deleteAllBookings(): Action
    {
        return Action::make('deleteAllBookings')
            ->label(__('Delete all bookings'))
            ->icon(Heroicon::OutlinedTrash)
            ->color('danger')
            ->requiresConfirmation()
            ->modalDescription(__('This deletes every slot of this event, including booked ones. Pilots are not notified.'))
            ->visible(fn (mixed $record, Component $livewire): bool => self::event($record, $livewire)->startEvent > now())
            ->action(function (mixed $record, Component $livewire): void {
                $event = self::event($record, $livewire);

                activity()
                    ->by(auth()->user())
                    ->on($event)
                    ->log('Delete all bookings');

                $event->bookings()->delete();

                Notification::make()->title(__('All bookings have been deleted'))->success()->send();
            });
    }

    public static function canAddManualSlots(Event $event): bool
    {
        return $event->allowsManualBookings() && $event->endEvent > now();
    }

    /**
     * A searchable airport select for modal forms, which have no relationship to bind to.
     */
    public static function airportSelect(string $name, string $label): Select
    {
        return Select::make($name)
            ->label($label)
            ->searchable()
            ->getSearchResultsUsing(fn (string $search): array => Airport::query()
                ->search($search)
                ->limit(50)
                ->get()
                ->mapWithKeys(fn (Airport $airport): array => [$airport->id => $airport->dropdownLabel()])
                ->all())
            ->getOptionLabelUsing(fn (mixed $value): ?string => Airport::find($value)?->dropdownLabel());
    }

    public static function time(string $name, string $label): TimePicker
    {
        return TimePicker::make($name)
            ->label($label)
            ->seconds(false)
            ->format('H:i')
            ->suffix('z');
    }

    /**
     * The booking actions are written for a form request, so build one from the validated form data.
     *
     * @param  array<string, mixed>  $data
     */
    public static function storeBookingRequest(Event $event, array $data): StoreBooking
    {
        return StoreBooking::create('', 'POST', [
            'id' => $event->id,
            'bulk' => false,
            ...array_map(fn (mixed $value): mixed => is_bool($value) ? (int) $value : $value, $data),
        ]);
    }
}

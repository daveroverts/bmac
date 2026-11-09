<?php

namespace App\Filament\Resources\Events\Pages;

use App\Actions\Booking\BulkUpdateBookingsAction;
use App\Actions\Booking\CreateBookingsAction;
use App\Actions\Booking\DeleteBookingsAction;
use App\Actions\Booking\UpdateBookingAction;
use App\Enums\BookingStatus;
use App\Enums\EventType;
use App\Events\EventBulkEmail;
use App\Filament\Resources\Events\Actions\EventActions;
use App\Filament\Resources\Events\EventResource;
use App\Http\Requests\Booking\Admin\UpdateBooking;
use App\Models\Booking;
use App\Models\Event;
use App\Models\Flight;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ManageRelatedRecords;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Throwable;

/**
 * All slots of one event, with filters and the single and bulk booking actions.
 */
class ManageEventBookings extends ManageRelatedRecords
{
    protected static string $resource = EventResource::class;

    protected static string $relationship = 'bookings';

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedQueueList;

    public static function getNavigationLabel(): string
    {
        return __('Bookings');
    }

    public function getTitle(): string
    {
        return __('Bookings: :event', ['event' => $this->getEvent()->name]);
    }

    public function getEvent(): Event
    {
        /** @var Event $event */
        $event = $this->getRecord();

        return $event;
    }

    protected function getHeaderActions(): array
    {
        return [
            $this->addSlotAction(),
            EventActions::addTimeslots(),
            EventActions::importBookings(),
            EventActions::export(),
            ActionGroup::make([
                EventActions::autoAssign(),
                EventActions::routeAssign(),
                EventActions::sendBulkEmail(),
                EventActions::sendFinalInformation(),
                EventActions::deleteAllBookings(),
            ])
                ->label(__('More actions'))
                ->icon(Heroicon::OutlinedEllipsisVertical)
                ->button()
                ->color('gray'),
        ];
    }

    public function table(Table $table): Table
    {
        $event = $this->getEvent();
        $isMultiFlights = $this->isMultiFlights();

        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['user', 'flights.airportDep', 'flights.airportArr']))
            ->defaultSort(fn (Builder $query) => $query
                ->orderBy(Flight::query()
                    ->selectRaw('min(coalesce(eta, ctot))')
                    ->whereColumn('flights.booking_id', 'bookings.id'))
                ->orderBy('bookings.id'))
            ->defaultPaginationPageOption(50)
            ->paginationPageOptions([25, 50, 100, 'all'])
            ->striped()
            ->emptyStateHeading(__('No slots yet'))
            ->emptyStateDescription($event->allowsManualBookings()
                ? __('Add timeslots, a single slot, or import a file.')
                : __('Slots for this event type can only be added via import'))
            ->columns([
                TextColumn::make('callsign')
                    ->label(__('Callsign'))
                    ->placeholder('-')
                    ->weight('medium')
                    ->fontFamily('mono')
                    ->searchable(query: fn (Builder $query, string $search): Builder => $this->search($query, $search)),
                TextColumn::make('status')
                    ->label(__('Status'))
                    ->badge()
                    ->formatStateUsing(fn (BookingStatus $state): string => self::statusLabel($state))
                    ->color(fn (BookingStatus $state): string => match ($state) {
                        BookingStatus::BOOKED => 'success',
                        BookingStatus::RESERVED => 'warning',
                        BookingStatus::UNASSIGNED => 'gray',
                    }),
                TextColumn::make('user.full_name')
                    ->label(__('Pilot'))
                    ->description(fn (Booking $record): ?string => $record->user_id ? (string) $record->user_id : null)
                    ->placeholder('-')
                    ->state(fn (Booking $record): ?string => $record->user_id ? $record->user->full_name : null),
                TextColumn::make('legs')
                    ->label($isMultiFlights ? __('Flights') : __('Route'))
                    ->state(fn (Booking $record): array => $record->flights->map(fn (Flight $flight): string => sprintf(
                        '%s → %s',
                        $flight->airportDep?->icao ?? '?',
                        $flight->airportArr?->icao ?? '?',
                    ))->all())
                    ->listWithLineBreaks()
                    ->fontFamily('mono'),
                TextColumn::make('ctot')
                    ->label(__('CTOT'))
                    ->state(fn (Booking $record): array => $record->flights->map(fn (Flight $flight): string => $flight->ctot ? $flight->ctot->format('Hi') . 'z' : '-')->all())
                    ->listWithLineBreaks()
                    ->fontFamily('mono'),
                TextColumn::make('eta')
                    ->label(__('ETA'))
                    ->state(fn (Booking $record): array => $record->flights->map(fn (Flight $flight): string => $flight->eta ? $flight->eta->format('Hi') . 'z' : '-')->all())
                    ->listWithLineBreaks()
                    ->fontFamily('mono')
                    ->visible($event->uses_times)
                    ->toggleable(),
                TextColumn::make('acType')
                    ->label(__('Aircraft'))
                    ->placeholder('-')
                    ->fontFamily('mono'),
                TextColumn::make('oceanicFL')
                    ->label($event->is_oceanic_event ? __('Entry FL') : __('FL'))
                    ->toggleable(isToggledHiddenByDefault: ! $event->is_oceanic_event)
                    ->state(fn (Booking $record): ?string => $record->flights->first()?->oceanicFL ? 'FL' . $record->flights->first()->oceanicFL : null)
                    ->placeholder('-'),
                TextColumn::make('oceanicTrack')
                    ->label(__('Track'))
                    ->state(fn (Booking $record): ?string => $record->flights->first()?->oceanicTrack)
                    ->placeholder('-')
                    ->visible($event->is_oceanic_event)
                    ->toggleable(),
                TextColumn::make('route')
                    ->label(__('Route'))
                    ->state(fn (Booking $record): ?string => $record->flights->first()?->route)
                    ->placeholder('-')
                    ->limit(40)
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('notes')
                    ->label(__('Notes'))
                    ->state(fn (Booking $record): ?string => $record->flights->first()?->notes)
                    ->placeholder('-')
                    ->limit(40)
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('final_information_email_sent_at')
                    ->label(__('Final info'))
                    ->tooltip(__('Final information e-mail sent'))
                    ->state(fn (Booking $record): bool => $record->has_received_final_information_email)
                    ->boolean()
                    ->alignCenter()
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label(__('Status'))
                    ->options([
                        BookingStatus::UNASSIGNED->value => __('Open'),
                        BookingStatus::RESERVED->value => __('Reserved'),
                        BookingStatus::BOOKED->value => __('Booked'),
                    ]),
                TernaryFilter::make('final_information_email_sent_at')
                    ->label(__('Final information sent'))
                    ->nullable(),
                SelectFilter::make('dep')
                    ->label(__('Departure airport'))
                    ->options(fn (): array => $this->airportOptions('dep'))
                    ->query(fn (Builder $query, array $data) => $query->when($data['value'] ?? null, fn (Builder $query, $value) => $query->whereHas('flights', fn (Builder $query) => $query->where('dep', $value)))),
                SelectFilter::make('arr')
                    ->label(__('Arrival airport'))
                    ->options(fn (): array => $this->airportOptions('arr'))
                    ->query(fn (Builder $query, array $data) => $query->when($data['value'] ?? null, fn (Builder $query, $value) => $query->whereHas('flights', fn (Builder $query) => $query->where('arr', $value)))),
            ])
            ->recordActions([
                $this->editBookingAction(),
                Action::make('delete')
                    ->label(__('Delete'))
                    ->icon(Heroicon::OutlinedTrash)
                    ->iconButton()
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalDescription(fn (Booking $record): string => $record->user_id
                        ? __(':pilot will get an e-mail that their booking was deleted.', ['pilot' => $record->user->full_name])
                        : __('This slot has no pilot.'))
                    ->visible(fn (): bool => $this->canChangeBookings())
                    ->action(function (Booking $record, DeleteBookingsAction $deleteBookings): void {
                        $failed = $deleteBookings->handle($this->getEvent(), new Collection([$record]));
                        $this->notifyResult(__('Booking has been deleted.'), $failed);
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    $this->bulkEditAction(),
                    $this->bulkEmailAction(),
                    $this->bulkDeleteAction(),
                ])->label(__('Bulk actions')),
            ])
            ->checkIfRecordIsSelectableUsing(fn (): bool => true);
    }

    private function addSlotAction(): Action
    {
        return Action::make('addSlot')
            ->label(__('Add slot'))
            ->icon(Heroicon::OutlinedPlus)
            ->visible(fn (): bool => EventActions::canAddManualSlots($this->getEvent()))
            ->fillForm(fn (): array => [
                'dep' => $this->getEvent()->dep,
                'arr' => $this->getEvent()->arr,
                'is_callsign_editable' => true,
                'is_actype_editable' => true,
            ])
            ->schema([
                Grid::make(2)->schema([
                    TextInput::make('callsign')->label(__('Callsign'))->alphaNum()->minLength(4)->maxLength(7),
                    TextInput::make('acType')->label(__('Aircraft code'))->alphaNum()->minLength(3)->maxLength(4),
                    EventActions::airportSelect('dep', __('Departure airport'))->required(),
                    EventActions::airportSelect('arr', __('Arrival airport'))->required(),
                    EventActions::time('ctot', __('CTOT')),
                    EventActions::time('eta', __('ETA')),
                    TextInput::make('oceanicFL')
                        ->label($this->getEvent()->is_oceanic_event ? __('Oceanic Entry Level') : __('Cruise FL'))
                        ->integer()
                        ->prefix('FL'),
                ]),
                Textarea::make('route')->label(__('Route')),
                Textarea::make('notes')->label(__('Notes')),
                Grid::make(2)->schema([
                    Toggle::make('is_callsign_editable')->label(__('Callsign editable?')),
                    Toggle::make('is_actype_editable')->label(__('Aircraft code editable?')),
                ]),
            ])
            ->action(function (array $data, CreateBookingsAction $createBookings): void {
                $event = $this->getEvent();
                $createBookings->handle(EventActions::storeBookingRequest($event, $data), $event);

                Notification::make()->title(__('Slot created'))->success()->send();
            });
    }

    private function editBookingAction(): Action
    {
        $event = $this->getEvent();

        return Action::make('edit')
            ->label(__('Edit'))
            ->icon(Heroicon::OutlinedPencilSquare)
            ->iconButton()
            ->modalHeading(fn (Booking $record): string => __('Edit :callsign', ['callsign' => $record->formatted_callsign]))
            ->modalDescription($this->isMultiFlights() ? __('Multi flights: only the first leg can be edited here.') : null)
            ->visible(fn (): bool => $this->canChangeBookings())
            ->fillForm(function (Booking $record): array {
                /** @var Flight|null $flight */
                $flight = $record->flights->first();

                return [
                    'is_callsign_editable' => $record->is_callsign_editable,
                    'is_actype_editable' => $record->is_actype_editable,
                    'callsign' => $record->callsign,
                    'acType' => $record->acType,
                    'ctot' => $flight?->ctot?->format('H:i'),
                    'eta' => $flight?->eta?->format('H:i'),
                    'dep' => $flight?->dep,
                    'arr' => $flight?->arr,
                    'route' => $flight?->route,
                    'oceanicTrack' => $flight?->oceanicTrack,
                    'oceanicFL' => $flight?->oceanicFL,
                    'notes' => $flight?->notes,
                    'notify_user' => true,
                ];
            })
            ->schema([
                Grid::make(2)->schema([
                    TextInput::make('callsign')->label(__('Callsign'))->alphaNum()->maxLength(7),
                    TextInput::make('acType')->label(__('Aircraft code'))->alphaNum()->minLength(3)->maxLength(4),
                    EventActions::airportSelect('dep', __('Departure airport')),
                    EventActions::airportSelect('arr', __('Arrival airport')),
                    EventActions::time('ctot', __('CTOT')),
                    EventActions::time('eta', __('ETA')),
                    TextInput::make('oceanicTrack')
                        ->label(__('Track'))
                        ->alpha()
                        ->maxLength(2)
                        ->visible($event->is_oceanic_event),
                    TextInput::make('oceanicFL')
                        ->label($event->is_oceanic_event ? __('Oceanic Entry Level') : __('Cruise FL'))
                        ->integer()
                        ->prefix('FL'),
                ]),
                Textarea::make('route')->label(__('Route')),
                Textarea::make('notes')->label(__('Notes')),
                Grid::make(2)->schema([
                    Toggle::make('is_callsign_editable')->label(__('Callsign editable?')),
                    Toggle::make('is_actype_editable')->label(__('Aircraft code editable?')),
                ]),
                Section::make(__('Pilot'))
                    ->description(fn (Booking $record): string => sprintf('%s (%d)', $record->user->full_name, $record->user_id))
                    ->visible(fn (Booking $record): bool => (bool) $record->user_id)
                    ->compact()
                    ->schema([
                        Toggle::make('notify_user')->label(__('Notify user?'))->live(),
                        Textarea::make('message')
                            ->label(__('Message'))
                            ->helperText(__('Added to the change e-mail.'))
                            ->visible(fn (Get $get): bool => (bool) $get('notify_user')),
                    ]),
            ])
            ->action(function (array $data, Booking $record, UpdateBookingAction $updateBooking): void {
                $request = UpdateBooking::create('', 'POST', array_map(
                    fn (mixed $value): mixed => is_bool($value) ? (int) $value : $value,
                    [...$data, 'notify_user' => ($data['notify_user'] ?? false) ?: null],
                ));

                $updateBooking->handle($request, $record);

                Notification::make()->title(__('Booking has been changed!'))->success()->send();
            });
    }

    /**
     * Change one or more fields on every selected booking. Only fields that are switched on are touched.
     */
    private function bulkEditAction(): BulkAction
    {
        $event = $this->getEvent();
        $isMultiFlights = $this->isMultiFlights();

        $field = fn (string $name, string $label, mixed $input): Fieldset => Fieldset::make($label)
            ->columns(1)
            ->schema([
                Toggle::make("change.$name")->label(__('Change :field', ['field' => mb_strtolower($label)]))->live(),
                $input->hiddenLabel()->visible(fn (Get $get): bool => (bool) $get("change.$name")),
            ]);

        return BulkAction::make('bulkEdit')
            ->label(__('Edit selected'))
            ->icon(Heroicon::OutlinedPencilSquare)
            ->modalHeading(fn (Collection $records): string => trans_choice('{1} Edit :count booking|[2,*] Edit :count bookings', $records->count(), ['count' => $records->count()]))
            ->modalDescription(__('Switch on the fields you want to change. Everything else stays as it is. An empty value clears the field.'))
            ->visible(fn (): bool => $this->canChangeBookings())
            ->fillForm(['notifyUsers' => true, 'shiftDirection' => 'later'])
            ->schema(array_values(array_filter([
                Grid::make(2)->schema(array_values(array_filter([
                    $field('acType', __('Aircraft code'), TextInput::make('acType')->alphaNum()->minLength(3)->maxLength(4)),
                    $isMultiFlights ? null : $field('oceanicFL', __('Cruise FL'), TextInput::make('oceanicFL')->integer()->maxLength(3)->prefix('FL')),
                    $field('is_callsign_editable', __('Callsign editable?'), Toggle::make('is_callsign_editable')),
                    $field('is_actype_editable', __('Aircraft code editable?'), Toggle::make('is_actype_editable')),
                ]))),
                $isMultiFlights ? null : $field('route', __('Route'), Textarea::make('route')),
                $isMultiFlights ? null : $field('notes', __('Notes'), Textarea::make('notes')),
                $event->uses_times ? Fieldset::make(__('Times'))->columns(2)->schema([
                    Toggle::make('change.timeShift')->label(__('Shift CTOT and ETA'))->live()->columnSpanFull(),
                    Select::make('shiftDirection')
                        ->hiddenLabel()
                        ->options(['later' => __('Later'), 'earlier' => __('Earlier')])
                        ->native(false)
                        ->visible(fn (Get $get): bool => (bool) $get('change.timeShift')),
                    TextInput::make('shiftMinutes')
                        ->hiddenLabel()
                        ->integer()
                        ->minValue(1)
                        ->maxValue(1439)
                        ->suffix(__('minutes'))
                        ->required(fn (Get $get): bool => (bool) $get('change.timeShift'))
                        ->visible(fn (Get $get): bool => (bool) $get('change.timeShift')),
                ]) : null,
                Section::make(__('Pilots'))->compact()->schema([
                    Toggle::make('notifyUsers')->label(__('E-mail affected pilots about the change'))->live(),
                    Textarea::make('changeMessage')
                        ->label(__('Message'))
                        ->visible(fn (Get $get): bool => (bool) $get('notifyUsers')),
                ]),
            ])))
            ->action(function (array $data, Collection $records, BulkUpdateBookingsAction $bulkUpdate, BulkAction $action): void {
                $changed = array_keys(array_filter($data['change'] ?? []));

                if ($changed === []) {
                    Notification::make()->title(__('Switch on at least one field to change.'))->warning()->send();
                    $action->halt();
                }

                $values = [];

                foreach ($changed as $name) {
                    $values[$name] = match ($name) {
                        'is_callsign_editable', 'is_actype_editable' => (bool) ($data[$name] ?? false),
                        'timeShift' => ($data['shiftDirection'] === 'earlier' ? -1 : 1) * (int) $data['shiftMinutes'],
                        default => filled($data[$name] ?? null) ? $data[$name] : null,
                    };
                }

                /** @var Collection<int, Booking> $records */
                $records->load('flights');
                $failed = $bulkUpdate->handle($records, $values, (bool) $data['notifyUsers'], ($data['changeMessage'] ?? null) ?: null);

                $this->notifyResult(trans_choice('{1} :count booking has been changed.|[2,*] :count bookings have been changed.', $records->count(), ['count' => $records->count()]), $failed);
            })
            ->deselectRecordsAfterCompletion();
    }

    private function bulkEmailAction(): BulkAction
    {
        return BulkAction::make('bulkEmail')
            ->label(__('E-mail selected'))
            ->icon(Heroicon::OutlinedEnvelope)
            ->modalHeading(fn (Collection $records): string => trans_choice('{1} E-mail the pilot of :count booking|[2,*] E-mail the pilots of :count bookings', $records->count(), ['count' => $records->count()]))
            ->modalDescription(__('Open slots are skipped. A pilot with several selected bookings gets one e-mail.'))
            ->modalSubmitActionLabel(__('Send'))
            ->schema([
                TextInput::make('subject')->label(__('Subject'))->required(),
                Textarea::make('message')->label(__('Message'))->required()->rows(6)->helperText(__('Salutation and closing are already included')),
                Toggle::make('testmode')->label(__('Test mode: only send it to myself')),
            ])
            ->action(function (array $data, Collection $records, BulkAction $action): void {
                $request = ['subject' => $data['subject'], 'message' => nl2br(e($data['message']))];
                $event = $this->getEvent();

                if ($data['testmode'] ?? false) {
                    event(new EventBulkEmail($event, [...$request, 'testmode' => true], User::query()->whereKey(auth()->id())->get()));
                    Notification::make()->title(__('Email has been sent to yourself'))->success()->send();
                    $action->halt();
                }

                $users = User::query()->whereKey($records->pluck('user_id')->filter()->unique())->orderBy('id')->get();

                if ($users->isEmpty()) {
                    Notification::make()->title(__('None of the selected bookings have a pilot.'))->warning()->send();
                    $action->halt();
                }

                $failed = 0;

                foreach ($users as $user) {
                    try {
                        event(new EventBulkEmail($event, $request, new Collection([$user])));
                    } catch (Throwable $throwable) {
                        report($throwable);
                        $failed++;
                    }
                }

                $this->notifyResult(trans_choice('{1} Email has been sent to :count pilot.|[2,*] Email has been sent to :count pilots.', $users->count() - $failed, ['count' => $users->count() - $failed]), $failed);
            })
            ->deselectRecordsAfterCompletion();
    }

    private function bulkDeleteAction(): BulkAction
    {
        return BulkAction::make('bulkDelete')
            ->label(__('Delete selected'))
            ->icon(Heroicon::OutlinedTrash)
            ->color('danger')
            ->requiresConfirmation()
            ->modalDescription(function (Collection $records): string {
                $pilots = $records->pluck('user_id')->filter()->unique()->count();

                return $pilots > 0
                    ? trans_choice('{1} :count pilot will get an e-mail that their booking was deleted.|[2,*] :count pilots will get an e-mail that their booking was deleted.', $pilots, ['count' => $pilots])
                    : __('None of the selected slots have a pilot.');
            })
            ->visible(fn (): bool => $this->canChangeBookings())
            ->action(function (Collection $records, DeleteBookingsAction $deleteBookings): void {
                /** @var Collection<int, Booking> $records */
                $failed = $deleteBookings->handle($this->getEvent(), $records);

                $this->notifyResult(trans_choice('{1} :count booking has been deleted.|[2,*] :count bookings have been deleted.', $records->count(), ['count' => $records->count()]), $failed);
            })
            ->deselectRecordsAfterCompletion();
    }

    /**
     * Search on callsign, pilot name and CID; every word must match.
     *
     * @param  Builder<Booking>  $query
     * @return Builder<Booking>
     */
    private function search(Builder $query, string $search): Builder
    {
        foreach (preg_split('/\s+/', trim($search)) ?: [] as $word) {
            $term = '%' . mb_strtolower($word) . '%';

            $query->where(function (Builder $query) use ($term, $word): void {
                $query->whereRaw('LOWER(callsign) LIKE ?', [$term])
                    ->when(ctype_digit($word), fn (Builder $query) => $query->orWhere('user_id', $word))
                    ->orWhereHas('user', fn (Builder $query) => $query
                        ->whereRaw('LOWER(name_first) LIKE ?', [$term])
                        ->orWhereRaw('LOWER(name_last) LIKE ?', [$term]));
            });
        }

        return $query;
    }

    /**
     * @return array<int, string>
     */
    private function airportOptions(string $column): array
    {
        return Flight::query()
            ->whereIn('booking_id', $this->getEvent()->bookings()->select('id'))
            ->with($column === 'dep' ? 'airportDep' : 'airportArr')
            ->get()
            ->unique($column)
            ->mapWithKeys(fn (Flight $flight): array => [$flight->{$column} => ($column === 'dep' ? $flight->airportDep : $flight->airportArr)?->icao ?? '?'])
            ->sort()
            ->all();
    }

    private function notifyResult(string $title, int $failedNotifications): void
    {
        Notification::make()
            ->title($title)
            ->body($failedNotifications > 0 ? trans_choice('{1} However, :count pilot could not be notified.|[2,*] However, :count pilots could not be notified.', $failedNotifications, ['count' => $failedNotifications]) : null)
            ->status($failedNotifications > 0 ? 'warning' : 'success')
            ->send();
    }

    private function canChangeBookings(): bool
    {
        return $this->getEvent()->endEvent >= now();
    }

    private function isMultiFlights(): bool
    {
        return (int) $this->getEvent()->event_type_id === EventType::MULTIFLIGHTS->value;
    }

    public static function statusLabel(BookingStatus $status): string
    {
        return match ($status) {
            BookingStatus::UNASSIGNED => __('Open'),
            BookingStatus::RESERVED => __('Reserved'),
            BookingStatus::BOOKED => __('Booked'),
        };
    }
}

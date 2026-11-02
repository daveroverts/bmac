<?php

namespace App\Livewire\Booking\Admin;

use App\Actions\Booking\BulkUpdateBookingsAction;
use App\Enums\BookingStatus;
use App\Enums\EventType;
use App\Events\BookingDeleted;
use App\Events\EventBulkEmail;
use App\Models\Booking;
use App\Models\Event;
use App\Models\Flight;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as SupportCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Js;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Throwable;

/**
 * @property-read LengthAwarePaginator<int, Booking> $bookings
 * @property-read Collection<int, Booking> $selectedBookings
 */
class Overview extends Component
{
    use WithPagination;

    private const array PER_PAGE_OPTIONS = [25, 50, 100];

    protected string $paginationTheme = 'bootstrap';

    public Event $event;

    #[Url(except: '')]
    public string $search = '';

    #[Url(except: 'all')]
    public string $status = 'all';

    #[Url(except: 25)]
    public int $perPage = 25;

    /** @var array<int, int|string> */
    public array $selected = [];

    public ?string $panel = null;

    /** @var array<string, bool> */
    public array $enabledFields = [];

    public string $acType = '';

    public string $isCallsignEditable = '';

    public string $isActypeEditable = '';

    public string $route = '';

    public string $oceanicFL = '';

    public string $notes = '';

    public string $shiftDirection = 'later';

    public string $shiftMinutes = '';

    public bool $notifyUsers = true;

    public string $changeMessage = '';

    public string $emailSubject = '';

    public string $emailMessage = '';

    public bool $emailTestMode = false;

    public ?string $emailFeedback = null;

    public bool $emailFailed = false;

    public function boot(): void
    {
        abort_unless(auth()->user()?->isAdmin, 404);
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedStatus(): void
    {
        $this->resetPage();
    }

    public function updatedPerPage(): void
    {
        $this->resetPage();
    }

    public function updatedSelected(): void
    {
        if ($this->selected === []) {
            $this->closePanel();
        }
    }

    /**
     * Select every row on the current page, or clear them when all are already selected.
     */
    public function toggleVisible(): void
    {
        $visibleIds = $this->bookings->getCollection()->modelKeys();
        $selectedIds = array_map(intval(...), $this->selected);

        $this->selected = array_diff($visibleIds, $selectedIds) === []
            ? array_values(array_diff($selectedIds, $visibleIds))
            : array_values(array_unique([...$selectedIds, ...$visibleIds]));

        $this->updatedSelected();
    }

    /**
     * Select every booking that matches the current search and status filter, across all pages.
     */
    public function selectAllMatching(): void
    {
        $matchingIds = $this->filteredBookingsQuery()->pluck('bookings.id')->all();
        $selectedIds = array_map(intval(...), $this->selected);

        $this->selected = array_values(array_unique([...$selectedIds, ...$matchingIds]));
    }

    public function selectOnlyMatching(): void
    {
        $this->selected = $this->filteredBookingsQuery()
            ->whereKey(array_map(intval(...), $this->selected))
            ->pluck('bookings.id')
            ->all();

        $this->updatedSelected();
    }

    /**
     * Select or clear a shift-clicked range of rows in one update.
     *
     * @param  array<int, int|string>  $ids
     */
    public function selectRange(array $ids, bool $isSelected): void
    {
        $rangeIds = Booking::query()
            ->where('event_id', $this->event->id)
            ->whereKey(array_map(intval(...), $ids))
            ->pluck('id')
            ->all();
        $selectedIds = array_map(intval(...), $this->selected);

        $this->selected = $isSelected
            ? array_values(array_unique([...$selectedIds, ...$rangeIds]))
            : array_values(array_diff($selectedIds, $rangeIds));

        $this->updatedSelected();
    }

    public function clearSelection(): void
    {
        $this->selected = [];
        $this->closePanel();
    }

    public function openPanel(string $panel): void
    {
        $this->resetValidation();
        $this->reset('emailFeedback', 'emailFailed');
        $allowedPanels = $this->canChangeBookings() ? ['edit', 'email', 'delete'] : ['email'];

        $this->panel = in_array($panel, $allowedPanels, true) ? $panel : null;
    }

    /**
     * Discards the edit draft, so switches left on cannot clear fields on a later selection.
     * The email subject and message are kept, so an accidental close does not lose a written message.
     */
    public function closePanel(): void
    {
        $this->resetValidation();
        $this->reset(
            'panel',
            'enabledFields',
            'acType',
            'isCallsignEditable',
            'isActypeEditable',
            'route',
            'oceanicFL',
            'notes',
            'shiftDirection',
            'shiftMinutes',
            'notifyUsers',
            'changeMessage',
            'emailTestMode',
            'emailFeedback',
            'emailFailed',
        );
    }

    public function applyChanges(BulkUpdateBookingsAction $action): void
    {
        if (! $this->canChangeBookings()) {
            $this->notifyEventEnded();

            return;
        }

        $enabledFields = $this->enabledFieldNames();

        if ($enabledFields === []) {
            $this->addError('enabledFields', __('Switch on at least one field to change.'));

            return;
        }

        $validated = $this->validate(
            array_intersect_key([
                'acType' => ['nullable', 'alpha_num', 'between:3,4'],
                'isCallsignEditable' => ['required', 'in:0,1'],
                'isActypeEditable' => ['required', 'in:0,1'],
                'route' => ['nullable', 'string'],
                'oceanicFL' => ['nullable', 'digits_between:1,3'],
                'notes' => ['nullable', 'string'],
                'shiftMinutes' => ['required', 'integer', 'between:1,1439'],
            ], array_flip($enabledFields)) + [
                'shiftDirection' => ['in:later,earlier'],
                'changeMessage' => ['nullable', 'string'],
            ],
            [],
            [
                'acType' => __('Aircraft code'),
                'isCallsignEditable' => __('Callsign editable?'),
                'isActypeEditable' => __('Aircraft code editable?'),
                'route' => __('Route'),
                'oceanicFL' => __('Cruise FL'),
                'notes' => __('Notes'),
                'shiftMinutes' => __('Minutes'),
                'changeMessage' => __('Message'),
            ],
        );

        $bookings = $this->selectedBookings;

        if ($bookings->isEmpty()) {
            $this->notifySelectionGone();

            return;
        }

        $failedNotifications = $action->handle(
            $bookings,
            $this->actionValues($validated),
            $this->notifyUsers,
            $validated['changeMessage'] ?: null,
        );

        $this->finishBulkAction(__('Bookings changed'), trans_choice('{1} :count booking has been changed.|[2,*] :count bookings have been changed.', $bookings->count(), ['count' => $bookings->count()]), $failedNotifications);
    }

    public function sendEmail(): void
    {
        $this->reset('emailFeedback', 'emailFailed');
        $validated = $this->validate([
            'emailSubject' => ['required', 'string'],
            'emailMessage' => ['required', 'string'],
        ], [], [
            'emailSubject' => __('Subject'),
            'emailMessage' => __('Message'),
        ]);

        $request = [
            'subject' => $validated['emailSubject'],
            'message' => nl2br(e($validated['emailMessage'])),
        ];

        if ($this->emailTestMode) {
            try {
                event(new EventBulkEmail($this->event, [...$request, 'testmode' => true], User::query()->whereKey(auth()->id())->get()));
                $this->emailFeedback = __('Email has been sent to yourself');
            } catch (Throwable $throwable) {
                report($throwable);
                $this->emailFailed = true;
                $this->emailFeedback = __('The test email could not be sent. It may already have been delivered; check before retrying.');
            }

            return;
        }

        $bookings = $this->selectedBookings;
        $users = User::query()->whereKey($bookings->pluck('user_id')->filter()->unique())->orderBy('id')->get();

        if ($users->isEmpty()) {
            $this->addError('emailSubject', __('None of the selected bookings have a pilot.'));

            return;
        }

        foreach ($users as $user) {
            try {
                event(new EventBulkEmail($this->event, $request, new Collection([$user])));
            } catch (Throwable $throwable) {
                report($throwable);
                $this->emailFailed = true;
                $this->emailFeedback = __('The email could not be sent to every pilot. Successfully submitted pilots have been removed from the selection. Remaining pilots may already have received it; check before retrying.');

                return;
            }

            $submittedBookingIds = $bookings->where('user_id', $user->id)->modelKeys();
            $this->selected = array_values(array_diff($this->selected, $submittedBookingIds));
            unset($this->selectedBookings);
        }

        $this->finishBulkAction(__('Done'), trans_choice('{1} Email has been sent to :count pilot.|[2,*] Email has been sent to :count pilots.', $users->count(), ['count' => $users->count()]));
    }

    public function deleteSelected(): void
    {
        if (! $this->canChangeBookings()) {
            $this->notifyEventEnded();

            return;
        }

        $bookings = $this->selectedBookings;

        if ($bookings->isEmpty()) {
            $this->notifySelectionGone();

            return;
        }

        $users = $bookings->whereNotNull('user_id')->pluck('user')->unique('id')->values();

        DB::transaction(function () use ($bookings): void {
            $bookings->each->delete();

            activity()
                ->by(auth()->user())
                ->on($this->event)
                ->withProperties(['count' => $bookings->count(), 'bookings' => $bookings->modelKeys()])
                ->log('Delete selected bookings');
        });

        $failedNotifications = 0;

        foreach ($users as $user) {
            $callsigns = $bookings->where('user_id', $user->id)->map(fn (Booking $booking): string => $booking->formatted_callsign)->values()->all();

            try {
                event(new BookingDeleted($this->event, $user, $callsigns));
            } catch (Throwable $throwable) {
                report($throwable);
                $failedNotifications++;
            }
        }

        $this->finishBulkAction(__('Bookings deleted'), trans_choice('{1} :count booking has been deleted.|[2,*] :count bookings have been deleted.', $bookings->count(), ['count' => $bookings->count()]), $failedNotifications);
    }

    /**
     * @return LengthAwarePaginator<int, Booking>
     */
    #[Computed]
    public function bookings(): LengthAwarePaginator
    {
        return $this->filteredBookingsQuery()
            ->with(['user', 'flights.airportDep', 'flights.airportArr'])
            ->orderBy(
                Flight::query()
                    ->selectRaw('min(coalesce(eta, ctot))')
                    ->whereColumn('flights.booking_id', 'bookings.id')
            )
            ->orderBy('id')
            ->paginate($this->perPageValue());
    }

    /**
     * The event scope keeps a tampered selection from reaching another event's bookings.
     *
     * @return Collection<int, Booking>
     */
    #[Computed]
    public function selectedBookings(): Collection
    {
        if ($this->selected === []) {
            return new Collection();
        }

        return Booking::query()
            ->where('event_id', $this->event->id)
            ->with(['user', 'flights'])
            ->whereKey(array_map(intval(...), $this->selected))
            ->get();
    }

    public function render(): View
    {
        $statusCounts = $this->event->bookings()->toBase()
            ->selectRaw('status, count(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        $selectedBookings = $this->selectedBookings;
        $selectedIds = $selectedBookings->modelKeys();
        $visibleIds = $this->bookings->getCollection()->modelKeys();
        $allVisibleSelected = $visibleIds !== [] && array_diff($visibleIds, $selectedIds) === [];
        $matchingCount = $this->bookings->total();
        $selectedMatchingCount = $selectedIds === []
            ? 0
            : $this->filteredBookingsQuery()->whereKey($selectedIds)->count();

        return view('livewire.booking.admin.overview', [
            'bookings' => $this->bookings,
            'canChangeBookings' => $this->canChangeBookings(),
            'isMultiFlights' => $this->isMultiFlights(),
            'perPageOptions' => self::PER_PAGE_OPTIONS,
            'filters' => [
                'all' => [__('All'), $statusCounts->sum()],
                'open' => [__('Open'), $statusCounts->get(BookingStatus::UNASSIGNED->value, 0)],
                'reserved' => [__('Reserved'), $statusCounts->get(BookingStatus::RESERVED->value, 0)],
                'booked' => [__('Booked'), $statusCounts->get(BookingStatus::BOOKED->value, 0)],
            ],
            'selectedBookings' => $selectedBookings,
            'selectedCount' => $selectedBookings->count(),
            'bookedCount' => $selectedBookings->where('status', BookingStatus::BOOKED)->count(),
            'reservedCount' => $selectedBookings->where('status', BookingStatus::RESERVED)->count(),
            'openCount' => $selectedBookings->where('status', BookingStatus::UNASSIGNED)->count(),
            'affectedPilotCount' => $selectedBookings->pluck('user_id')->filter()->unique()->count(),
            'fieldSummaries' => $this->panel === 'edit' ? $this->editFieldSummaries($selectedBookings) : [],
            'selectedMatchingCount' => $selectedMatchingCount,
            'hiddenByFilterCount' => count($selectedIds) - $selectedMatchingCount,
            'otherPageCount' => $selectedMatchingCount - count(array_intersect($visibleIds, $selectedIds)),
            'allVisibleSelected' => $allVisibleSelected,
            'visibleCount' => count($visibleIds),
            'matchingCount' => $matchingCount,
            'canSelectAllMatching' => $allVisibleSelected && $selectedMatchingCount < $matchingCount,
            'allMatchingSelected' => $allVisibleSelected && $matchingCount > count($visibleIds) && $selectedMatchingCount === $matchingCount,
        ]);
    }

    /**
     * Uses "!" as the LIKE escape character because SQLite has no default one.
     *
     * @param  Builder<Booking>  $query
     */
    private function whereMatchesWord(Builder $query, string $word): void
    {
        $term = '%' . str_replace(['!', '%', '_'], ['!!', '!%', '!_'], $word) . '%';

        $query->where(function (Builder $query) use ($term, $word): void {
            $query->whereRaw("callsign like ? escape '!'", [$term])
                ->when(ctype_digit($word), function (Builder $query) use ($word): void {
                    $query->orWhere('user_id', $word);
                })
                ->orWhereHas('user', function (Builder $query) use ($term): void {
                    $query->whereRaw("name_first like ? escape '!'", [$term])
                        ->orWhereRaw("name_last like ? escape '!'", [$term]);
                });
        });
    }

    /**
     * @return array<int, string>
     */
    private function enabledFieldNames(): array
    {
        $fields = array_keys(array_filter($this->enabledFields));

        $editableFields = $this->isMultiFlights()
            ? ['acType', 'isCallsignEditable', 'isActypeEditable']
            : ['acType', 'isCallsignEditable', 'isActypeEditable', 'route', 'oceanicFL', 'notes'];

        if ($this->event->uses_times) {
            $editableFields[] = 'shiftMinutes';
        }

        return array_values(array_intersect($fields, $editableFields));
    }

    /**
     * Describes what the selected bookings hold now for each editable field, and how many would lose a value if the field is cleared.
     * Flight fields read the first flight because only multiflights bookings have more than one, and those cannot edit flight fields.
     *
     * @param  Collection<int, Booking>  $bookings
     * @return array<string, array{hint: string, clearCount: int}>
     */
    private function editFieldSummaries(Collection $bookings): array
    {
        $summaries = [
            'acType' => $this->describeTextValues($bookings->pluck('acType')),
            'isCallsignEditable' => $this->describeFlagValues($bookings->pluck('is_callsign_editable')),
            'isActypeEditable' => $this->describeFlagValues($bookings->pluck('is_actype_editable')),
        ];

        if (! $this->isMultiFlights()) {
            foreach (['route', 'oceanicFL', 'notes'] as $field) {
                $summaries[$field] = $this->describeTextValues(
                    $bookings->map(fn (Booking $booking): mixed => $booking->flights->first()?->getAttribute($field)),
                );
            }
        }

        return $summaries;
    }

    /**
     * @param  SupportCollection<int, mixed>  $values
     * @return array{hint: string, clearCount: int}
     */
    private function describeTextValues(SupportCollection $values): array
    {
        $filled = $values->filter(fn (mixed $value): bool => filled($value))->map(fn (mixed $value): string => (string) $value);
        $emptyCount = $values->count() - $filled->count();
        $distinct = $filled->unique();

        $hint = match (true) {
            $filled->isEmpty() => __('Now: empty'),
            $distinct->count() === 1 && $emptyCount === 0 => __('Now: :value', ['value' => $distinct->first()]),
            $distinct->count() === 1 => trans_choice('{1} Now: :value, empty on :count booking|[2,*] Now: :value, empty on :count bookings', $emptyCount, ['value' => $distinct->first(), 'count' => $emptyCount]),
            $emptyCount === 0 => __('Now: :distinct different values', ['distinct' => $distinct->count()]),
            default => trans_choice('{1} Now: :distinct different values, empty on :count booking|[2,*] Now: :distinct different values, empty on :count bookings', $emptyCount, ['distinct' => $distinct->count(), 'count' => $emptyCount]),
        };

        return ['hint' => $hint, 'clearCount' => $filled->count()];
    }

    /**
     * @param  SupportCollection<int, mixed>  $values
     * @return array{hint: string, clearCount: int}
     */
    private function describeFlagValues(SupportCollection $values): array
    {
        $yesCount = $values->filter()->count();
        $noCount = $values->count() - $yesCount;

        $hint = match (true) {
            $noCount === 0 => __('Now: yes'),
            $yesCount === 0 => __('Now: no'),
            default => __('Now: yes on :yes, no on :no', ['yes' => $yesCount, 'no' => $noCount]),
        };

        return ['hint' => $hint, 'clearCount' => 0];
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    private function actionValues(array $validated): array
    {
        $values = [];

        foreach (['acType' => 'acType', 'route' => 'route', 'oceanicFL' => 'oceanicFL', 'notes' => 'notes'] as $field => $attribute) {
            if (array_key_exists($field, $validated)) {
                $values[$attribute] = filled($validated[$field]) ? $validated[$field] : null;
            }
        }

        foreach (['isCallsignEditable' => 'is_callsign_editable', 'isActypeEditable' => 'is_actype_editable'] as $field => $attribute) {
            if (array_key_exists($field, $validated)) {
                $values[$attribute] = (bool) $validated[$field];
            }
        }

        if (array_key_exists('shiftMinutes', $validated)) {
            $minutes = (int) $validated['shiftMinutes'];
            $values['timeShift'] = $this->shiftDirection === 'earlier' ? -$minutes : $minutes;
        }

        return $values;
    }

    /**
     * This event's bookings narrowed by the current status filter and search.
     *
     * @return Builder<Booking>
     */
    private function filteredBookingsQuery(): Builder
    {
        /** @var Builder<Booking> $query */
        $query = Booking::query()
            ->where('event_id', $this->event->id)
            ->when(in_array($this->status, ['open', 'reserved', 'booked'], true), function (Builder $query): void {
                $query->where('status', $this->statusFilterValue());
            })
            ->when(trim($this->search) !== '', function (Builder $query): void {
                foreach (preg_split('/\s+/', trim($this->search)) as $word) {
                    $this->whereMatchesWord($query, $word);
                }
            });

        return $query;
    }

    private function statusFilterValue(): int
    {
        return match ($this->status) {
            'reserved' => BookingStatus::RESERVED->value,
            'booked' => BookingStatus::BOOKED->value,
            default => BookingStatus::UNASSIGNED->value,
        };
    }

    private function perPageValue(): int
    {
        return in_array($this->perPage, self::PER_PAGE_OPTIONS, true) ? $this->perPage : self::PER_PAGE_OPTIONS[0];
    }

    private function canChangeBookings(): bool
    {
        return $this->event->endEvent >= now();
    }

    private function isMultiFlights(): bool
    {
        return (int) $this->event->event_type_id === EventType::MULTIFLIGHTS->value;
    }

    private function notifySelectionGone(): void
    {
        $this->clearSelection();
        unset($this->bookings, $this->selectedBookings);

        $this->notifyUser(__('Danger'), __('The selected bookings no longer exist.'), 'error');
    }

    private function notifyEventEnded(): void
    {
        $this->notifyUser(__('Danger'), __('Bookings can no longer be changed'), 'error');
    }

    /**
     * Always clears the selection once changes are saved, so a retry cannot apply a relative time shift twice.
     */
    private function finishBulkAction(string $title, string $text, int $failedNotifications = 0): void
    {
        $this->reset(
            'selected',
            'panel',
            'enabledFields',
            'acType',
            'isCallsignEditable',
            'isActypeEditable',
            'route',
            'oceanicFL',
            'notes',
            'shiftDirection',
            'shiftMinutes',
            'changeMessage',
            'emailSubject',
            'emailMessage',
            'emailTestMode',
            'emailFeedback',
            'emailFailed',
        );
        $this->resetPage();
        unset($this->bookings, $this->selectedBookings);

        if ($failedNotifications > 0) {
            $text .= ' ' . trans_choice('{1} However, :count pilot could not be notified.|[2,*] However, :count pilots could not be notified.', $failedNotifications, ['count' => $failedNotifications]);
        }

        $this->notifyUser($title, $text, $failedNotifications > 0 ? 'warning' : 'success');
    }

    private function notifyUser(string $title, string $text, string $icon): void
    {
        $this->js('Swal.fire(' . Js::from(['title' => $title, 'text' => $text, 'icon' => $icon]) . ')');
    }
}

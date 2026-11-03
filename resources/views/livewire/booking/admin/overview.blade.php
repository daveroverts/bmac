<div>
    <div class="my-3 d-flex flex-row flex-wrap align-items-center gap-3">
        <div class="input-group" style="max-width: 24rem;">
            <span class="input-group-text"><i class="fa fa-search"></i></span>
            <input
                type="search"
                class="form-control"
                placeholder="{{ __('Search callsign, pilot name or CID') }}"
                aria-label="{{ __('Search bookings') }}"
                wire:model.live.debounce.300ms="search"
            >
        </div>
        <select class="form-select w-auto d-sm-none" aria-label="{{ __('Filter bookings by status') }}" wire:model.live="status">
            @foreach ($filters as $value => [$label, $count])
                <option value="{{ $value }}">{{ $label }} ({{ $count }})</option>
            @endforeach
        </select>
        <div class="btn-group d-none d-sm-inline-flex" role="group" aria-label="{{ __('Filter bookings by status') }}">
            @foreach ($filters as $value => [$label, $count])
                <input type="radio" class="btn-check" name="status" id="status-{{ $value }}" autocomplete="off"
                    value="{{ $value }}" wire:model.live="status">
                <label class="btn btn-outline-primary" for="status-{{ $value }}">{{ $label }} {{ $count }}</label>
            @endforeach
        </div>
        <div class="ms-md-auto d-flex align-items-center gap-2">
            <label class="small text-nowrap" for="per-page">{{ __('Per page') }}</label>
            <select id="per-page" class="form-select form-select-sm w-auto" wire:model.live="perPage">
                @foreach ($perPageOptions as $option)
                    <option value="{{ $option }}">{{ $option }}</option>
                @endforeach
            </select>
        </div>
    </div>

    @unless ($canChangeBookings)
        <div class="alert alert-info">{{ __('This event has ended, so its bookings can no longer be changed. You can still email pilots.') }}</div>
    @endunless

    {{-- Sticky's default z-index (1020) sits above dropdown menus (1000); 10 still covers focused inputs and pagination in the rows it scrolls over. On phones it only sticks while bookings are selected. --}}
    {{-- Controls that remove themselves on click ask for focus here; it moves once Livewire has finished morphing, so it never lands on a removed or hidden element. "void" matters: Alpine calls a function returned from x-init, which would unhook immediately. --}}
    <section id="bulk-actions-bar" @class(['mb-2', 'sticky-top' => $selectedCount > 0, 'sticky-md-top' => $selectedCount === 0]) style="z-index: 10;" tabindex="-1" aria-label="{{ __('Bulk actions') }}"
        x-data="{ focusSelector: null }"
        x-init="void $wire.$hook('morphed', () => {
            if (! focusSelector) { return; }
            const target = document.querySelector(focusSelector);
            focusSelector = null;
            $nextTick(() => (target && target.offsetParent !== null ? target : $el).focus());
        })">
            <div @class([
                'p-2 px-3 rounded d-flex flex-wrap gap-2 align-items-center justify-content-between',
                'bg-primary text-white' => $selectedCount > 0,
                'bg-light border' => $selectedCount === 0,
            ])>
                <div style="flex: 1 1 20rem; min-width: 0;">
                    {{-- Only the count is live, so ticking rows announces a short update instead of the whole sentence. --}}
                    <strong aria-live="polite" aria-atomic="true">{{ $selectedCount > 0 ? trans_choice('{1} :count booking selected|[2,*] :count bookings selected', $selectedCount, ['count' => $selectedCount]) : __('No bookings selected') }}</strong>
                    @if ($selectedCount > 0)
                        <span class="small d-none d-md-inline">
                            <span aria-hidden="true">·</span>
                            {{-- Consequences belong to each action's panel; the bar only says who is on the selection. --}}
                            @if ($affectedPilotCount > 0)
                                {{ trans_choice('{1} :count pilot on these bookings.|[2,*] :count pilots on these bookings.', $affectedPilotCount, ['count' => $affectedPilotCount]) }}
                            @else
                                <span id="bulk-no-pilots">{{ __('No pilots on these bookings.') }}</span>
                            @endif
                        </span>
                        @if ($otherPageCount > 0)
                            <span class="small d-block">{{ trans_choice('{1} :count matching selected booking is on another page.|[2,*] :count matching selected bookings are on other pages.', $otherPageCount, ['count' => $otherPageCount]) }}</span>
                        @endif
                        @if ($hiddenByFilterCount > 0)
                            <div class="small mt-1" role="status">
                                {{ trans_choice('{1} :count selected booking is hidden by the current search or status filter.|[2,*] :count selected bookings are hidden by the current search or status filter.', $hiddenByFilterCount, ['count' => $hiddenByFilterCount]) }}
                                {{ __('Actions include these hidden selections.') }}
                                <button type="button" class="btn btn-sm btn-link text-white p-0 align-baseline" x-on:click="focusSelector = '#bulk-actions-bar'" wire:click="selectOnlyMatching">
                                    {{ $selectedMatchingCount > 0 ? trans_choice('{1} Keep only :count matching selected booking|[2,*] Keep only :count matching selected bookings', $selectedMatchingCount, ['count' => $selectedMatchingCount]) : __('Clear selection') }}
                                </button>
                            </div>
                        @endif
                    @else
                        <span class="small d-none d-md-inline">{{ $canChangeBookings ? __('Tick bookings to edit, email or delete several at once.') : __('Tick bookings to email their pilots.') }}</span>
                    @endif
                </div>
                <div class="d-flex flex-wrap gap-2 align-items-center">
                    @if ($selectedCount > 0)
                        <button type="button" class="btn btn-sm btn-link text-white" x-on:click="focusSelector = '#bulk-actions-bar'" wire:click="clearSelection">{{ __('Clear selection') }}</button>
                    @endif
                    @if ($canChangeBookings)
                    <button type="button" @class(['btn btn-sm', 'btn-light' => $selectedCount > 0, 'btn-outline-primary' => $selectedCount === 0])
                        id="bulk-open-edit" wire:click="openPanel('edit')" wire:loading.class="disabled" wire:loading.attr="aria-busy" wire:target="openPanel" aria-haspopup="dialog" @disabled($selectedCount === 0)>
                        <i class="fa fa-edit"></i> {{ __('Edit selected') }}
                    </button>
                    @endif
                    <button type="button" @class(['btn btn-sm', 'btn-light' => $selectedCount > 0, 'btn-outline-primary' => $selectedCount === 0])
                        id="bulk-open-email" wire:click="openPanel('email')" wire:loading.class="disabled" wire:loading.attr="aria-busy" wire:target="openPanel" aria-haspopup="dialog" @disabled($affectedPilotCount === 0)
                        @if ($selectedCount > 0 && $affectedPilotCount === 0) title="{{ __('None of the selected bookings has a pilot.') }}" aria-describedby="bulk-no-pilots" @endif>
                        <i class="fa fa-envelope"></i> {{ __('Email selected') }}
                    </button>
                    @if ($canChangeBookings)
                    <button type="button" @class(['btn btn-sm', 'btn-danger' => $selectedCount > 0, 'btn-outline-danger' => $selectedCount === 0])
                        id="bulk-open-delete" wire:click="openPanel('delete')" wire:loading.class="disabled" wire:loading.attr="aria-busy" wire:target="openPanel" aria-haspopup="dialog" @disabled($selectedCount === 0)>
                        <i class="fas fa-trash"></i> {{ __('Delete selected') }}
                    </button>
                    @endif
                </div>
            </div>
            {{-- Always rendered, so the confirmation is announced and focus has somewhere to land when the button goes away. --}}
            <div id="bulk-matching-banner" role="status" tabindex="-1" @class(['bg-body border rounded mt-1 px-3 py-1 small', 'd-none' => ! $canSelectAllMatching && ! $allMatchingSelected])>
                @if ($hiddenByFilterCount > 0)
                    {{ __('Selected: :matching matching; :total total.', ['matching' => $selectedMatchingCount, 'total' => $selectedCount]) }}
                @endif
                @if ($canSelectAllMatching)
                    {{ trans_choice('{1} The :count booking on this page is selected.|[2,*] All :count bookings on this page are selected.', $visibleCount, ['count' => $visibleCount]) }}
                    <button type="button" class="btn btn-link btn-sm p-0 align-baseline" x-on:click="focusSelector = '#bulk-matching-banner'" wire:click="selectAllMatching">{{ trans_choice('{1} Select the :count matching booking|[2,*] Select all :count matching bookings', $matchingCount, ['count' => $matchingCount]) }}</button>
                @elseif ($allMatchingSelected && $hiddenByFilterCount === 0)
                    {{ trans_choice('{1} The :count matching booking is selected.|[2,*] All :count matching bookings are selected.', $matchingCount, ['count' => $matchingCount]) }}
                @endif
            </div>
        </section>


    <div class="table-responsive position-relative" wire:loading.class="opacity-50" wire:loading.attr="aria-busy"
        wire:target="search,status,perPage,gotoPage,nextPage,previousPage,toggleVisible,selectAllMatching,selectOnlyMatching,selectRange">
        <table class="table table-hover align-middle bookings-admin-table">
            <thead>
                <tr>
                    <th scope="col">
                            <input type="checkbox" class="form-check-input" aria-label="{{ __('Select all bookings on this page') }}"
                                wire:click="toggleVisible" @checked($allVisibleSelected) @disabled($bookings->isEmpty()) wire:key="select-all"
                                x-data="{
                                    sync() {
                                        const selected = this.$wire.selected.map(String);
                                        const rows = [...this.$el.closest('table').querySelectorAll('input[data-row-select]')];
                                        const ticked = rows.filter((row) => selected.includes(row.value)).length;
                                        this.$el.checked = rows.length > 0 && ticked === rows.length;
                                        this.$el.indeterminate = ticked > 0 && ticked < rows.length;
                                    },
                                }"
                                x-effect="sync()"
                                x-init="void $wire.$hook('morphed', () => $nextTick(() => sync()))">
                        </th>
                    @if ($isMultiFlights)
                        <th scope="col">{{ __('Flight #1') }}</th>
                        <th scope="col">{{ __('Flight #2') }}</th>
                    @else
                        <th scope="col">{{ __('From') }}</th>
                        <th scope="col">{{ __('To') }}</th>
                        @if ($event->uses_times)
                            <th scope="col"><abbr title="{{ __('Calculated Take Off Time') }}">CTOT</abbr></th>
                            <th scope="col" class="d-none d-md-table-cell"><abbr title="{{ __('Estimated Time of Arrival') }}">ETA</abbr></th>
                        @endif
                    @endif
                    <th scope="col">{{ __('Callsign') }}</th>
                    <th scope="col" class="d-none d-md-table-cell">{{ __('Aircraft') }}</th>
                    <th scope="col">{{ __('Status') }}</th>
                    <th scope="col">{{ __('Pilot') }}</th>
                    <th scope="col" class="d-none d-lg-table-cell">{{ __('Final info sent') }}</th>
                    @if ($canChangeBookings)
                        <th scope="col"><span class="visually-hidden">{{ __('Actions') }}</span></th>
                    @endif
                </tr>
            </thead>
            <tbody x-data="{
                anchorId: null,
                toggleRow(event, checkbox) {
                    const boxes = [...this.$root.querySelectorAll('input[data-row-select]')];
                    const index = boxes.indexOf(checkbox);
                    const anchorIndex = boxes.findIndex((box) => box.value === this.anchorId);

                    if (event.shiftKey && anchorIndex !== -1 && anchorIndex !== index) {
                        event.preventDefault();
                        const [from, to] = [Math.min(anchorIndex, index), Math.max(anchorIndex, index)];
                        this.$wire.selectRange(boxes.slice(from, to + 1).map((box) => box.value), checkbox.checked);
                    }

                    this.anchorId = checkbox.value;
                },
            }">
                @forelse ($bookings as $booking)
                    @php($flight = $booking->flights->first())
                    @php($useMonospace = auth()->user()->use_monospace_font)
                    <tr wire:key="booking-{{ $booking->getKey() }}" @class(['table-active' => in_array($booking->getKey(), array_map('intval', $selected), true)])>
                            <td>
                                <input type="checkbox" class="form-check-input" value="{{ $booking->getKey() }}" data-row-select
                                    aria-label="{{ filled($booking->callsign)
                                        ? __('Select booking :callsign', ['callsign' => $booking->callsign])
                                        : __('Select slot :from to :to, :time', ['from' => $flight?->airportDep?->icao ?? '-', 'to' => $flight?->airportArr?->icao ?? '-', 'time' => $event->uses_times && $flight?->ctot ? $flight->formatted_ctot : '#' . $booking->getKey()]) }}"
                                    wire:model.live="selected" x-on:click="toggleRow($event, $el)" x-on:mousedown="if ($event.shiftKey) { $event.preventDefault(); }">
                            </td>
                        @if ($isMultiFlights)
                            <td><x-airport-ctot :booking="$booking" :order-by="1" /></td>
                            <td><x-airport-ctot :booking="$booking" :order-by="2" /></td>
                        @else
                            <td>
                                @if ($flight?->airportDep)
                                    <x-airport-name :airport="$flight->airportDep" />
                                @endif
                            </td>
                            <td>
                                @if ($flight?->airportArr)
                                    <x-airport-name :airport="$flight->airportArr" />
                                @endif
                            </td>
                            @if ($event->uses_times)
                                <td>{{ $flight?->formatted_ctot }}</td>
                                <td class="d-none d-md-table-cell">{{ $flight?->formatted_eta }}</td>
                            @endif
                        @endif
                        <td @class(['font-monospace' => $useMonospace])>{{ $booking->formatted_callsign }}</td>
                        <td @class(['d-none d-md-table-cell', 'font-monospace' => $useMonospace])>{{ $booking->formatted_actype }}</td>
                        <td>
                            @switch($booking->status)
                                @case(\App\Enums\BookingStatus::BOOKED)
                                    <span class="badge border bg-light text-body">{{ __('Booked') }}</span>
                                    @break
                                @case(\App\Enums\BookingStatus::RESERVED)
                                    <span class="badge border border-warning bg-warning-subtle text-warning-emphasis">{{ __('Reserved') }}</span>
                                    @break
                                @default
                                    <span class="badge border border-success bg-success-subtle text-success-emphasis">{{ __('Open') }}</span>
                            @endswitch
                        </td>
                        <td>
                            @if ($booking->user_id)
                                {{ $booking->user->pic }}
                                <a class="small text-body d-none d-md-block" href="mailto:{{ $booking->user->email }}">{{ $booking->user->email }}</a>
                            @else
                                <span class="text-muted">{{ __('None') }}</span>
                            @endif
                        </td>
                        <td class="small text-nowrap d-none d-lg-table-cell">
                            @if ($booking->final_information_email_sent_at)
                                {{ $booking->final_information_email_sent_at->format('j M, Hi\z') }}
                            @else
                                <span class="text-muted">{{ __('Not sent') }}</span>
                            @endif
                        </td>
                        @if ($canChangeBookings)
                            <td class="text-end">
                                <a href="{{ route('admin.bookings.edit', $booking) }}" class="btn btn-outline-primary btn-sm"><i class="fa fa-edit"></i> {{ __('Edit') }}</a>
                            </td>
                        @endif
                    </tr>
                @empty
                    <tr>
                        @php($columnCount = 6 + ($isMultiFlights ? 2 : 2 + ($event->uses_times ? 2 : 0)) + ($canChangeBookings ? 1 : 0))
                        <td colspan="{{ $columnCount }}" class="text-center py-4">
                            @if ($search !== '' || $status !== 'all')
                                {{ __('No bookings match this search or filter.') }}
                            @else
                                {{ __('This event has no bookings yet. Add or import slots to get started.') }}
                                @if ($canChangeBookings)
                                    <a href="{{ route('admin.events.bookings.import.create', $event) }}" class="d-block mt-2">{{ __('Import slots') }}</a>
                                @endif
                            @endif
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $bookings->links() }}

    @include('livewire.booking.admin.partials.bulk-modal')
</div>

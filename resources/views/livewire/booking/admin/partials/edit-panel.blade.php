<div class="modal-body">
<form id="bulk-edit-form" wire:submit="applyChanges">
    <p class="mb-2">{{ __('Switch on the fields you want to change. Fields that stay off keep each booking\'s current value. A field you switch on and leave empty is cleared.') }}</p>
    @error('enabledFields') <div class="alert alert-danger py-2">{{ $message }}</div> @enderror

    @php
        $textFields = ['acType' => __('Aircraft code')];
        $flightFields = $isMultiFlights ? [] : ['route' => __('Route'), 'oceanicFL' => $event->is_oceanic_event ? __('Oceanic Entry Level') : __('Cruise FL'), 'notes' => __('Notes')];
        $yesNoFields = ['isCallsignEditable' => __('Callsign editable?'), 'isActypeEditable' => __('Aircraft code editable?')];
    @endphp

    @foreach ($textFields + $yesNoFields + $flightFields as $field => $label)
        @php
            $isEnabled = $enabledFields[$field] ?? false;
            $summary = $fieldSummaries[$field] ?? null;
            $isTextField = ! array_key_exists($field, $yesNoFields);
        @endphp
        <div class="row g-2 align-items-center py-1" wire:key="bulk-field-{{ $field }}">
            <div class="col-md-4">
                <div class="form-check form-switch mb-0">
                    <input id="bulk-on-{{ $field }}" type="checkbox" role="switch" class="form-check-input" @if ($loop->first) data-autofocus @endif wire:model.live="enabledFields.{{ $field }}">
                    <label class="form-check-label" for="bulk-on-{{ $field }}">{{ $label }}</label>
                </div>
            </div>
            <div class="col-md-8">
                @php
                    $canClear = $isTextField && $isEnabled && ($summary['clearCount'] ?? 0) > 0;
                    $describedBy = trim(($summary ? "bulk-now-{$field}" : '') . ($canClear ? " bulk-clear-{$field}" : ''));
                @endphp
                @if ($isTextField)
                    <input type="text" wire:model="{{ $field }}" aria-label="{{ $label }}" @if ($describedBy !== '') aria-describedby="{{ $describedBy }}" @endif @disabled(! $isEnabled) @class(['form-control form-control-sm', 'is-invalid' => $errors->has($field)])>
                @else
                    <select wire:model="{{ $field }}" aria-label="{{ $label }}" @if ($describedBy !== '') aria-describedby="{{ $describedBy }}" @endif @disabled(! $isEnabled) @class(['form-select form-select-sm', 'is-invalid' => $errors->has($field)])>
                        <option value="">{{ __('Choose...') }}</option>
                        <option value="1">{{ __('Yes') }}</option>
                        <option value="0">{{ __('No') }}</option>
                    </select>
                @endif
                @error($field) <div class="invalid-feedback">{{ $message }}</div> @enderror
                @if ($summary)
                    <div id="bulk-now-{{ $field }}" class="form-text mt-1 text-break">{{ $summary['hint'] }}</div>
                @endif
                @if ($canClear)
                    {{-- Mirrors the server's filled() check, so whitespace counts as empty and "0" does not. --}}
                    <div id="bulk-clear-{{ $field }}" class="form-text mt-0 text-danger-emphasis" aria-live="polite">
                        <span x-show="String($wire.{{ $field }} ?? '').trim() === ''">
                            <i class="fas fa-exclamation-triangle" aria-hidden="true"></i>
                            {{ trans_choice('{1} Left empty, this clears the value on :count booking.|[2,*] Left empty, this clears the value on :count bookings.', $summary['clearCount'], ['count' => $summary['clearCount']]) }}
                        </span>
                    </div>
                @endif
            </div>
        </div>
    @endforeach

    @if ($event->uses_times)
    @php($isShiftEnabled = $enabledFields['shiftMinutes'] ?? false)
    <div class="row g-2 align-items-center py-1">
        <div class="col-md-4">
            <div class="form-check form-switch mb-0">
                <input id="bulk-on-shift" type="checkbox" role="switch" class="form-check-input" wire:model.live="enabledFields.shiftMinutes">
                <label class="form-check-label" for="bulk-on-shift">{{ __('Shift CTOT and ETA') }}</label>
            </div>
        </div>
        <div class="col-md-8 d-flex flex-wrap align-items-center gap-2">
            <select wire:model="shiftDirection" aria-label="{{ __('Direction') }}" class="form-select form-select-sm w-auto" @disabled(! $isShiftEnabled)>
                <option value="later">{{ __('Later') }}</option>
                <option value="earlier">{{ __('Earlier') }}</option>
            </select>
            <input type="number" min="1" max="1439" wire:model="shiftMinutes" aria-label="{{ __('Minutes') }}" style="width: 6rem;" @disabled(! $isShiftEnabled) @class(['form-control form-control-sm', 'is-invalid' => $errors->has('shiftMinutes')])>
            <span class="small">{{ __('minutes') }}</span>
            @error('shiftMinutes') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
        </div>
    </div>
    @endif

    <hr class="my-2">

    @if ($affectedPilotCount > 0)
        <div class="form-check mb-2">
            <input id="bulk-notify" type="checkbox" class="form-check-input" wire:model.live="notifyUsers">
            <label class="form-check-label" for="bulk-notify">{{ trans_choice('{1} Notify :count pilot of these changes|[2,*] Notify :count pilots of these changes', $affectedPilotCount, ['count' => $affectedPilotCount]) }}</label>
        </div>
        @if ($notifyUsers)
            <div class="mb-2">
                <label class="form-label small" for="bulk-change-message">{{ __('Message to pilots (optional)') }}</label>
                <textarea id="bulk-change-message" rows="2" wire:model="changeMessage" class="form-control form-control-sm"></textarea>
            </div>
        @endif
    @endif
</form>
</div>
<div class="modal-footer justify-content-between">
    <span class="small">{{ __('Applies to :count: :booked booked, :reserved reserved, :open open.', ['count' => $selectedCount, 'booked' => $bookedCount, 'reserved' => $reservedCount, 'open' => $openCount]) }}</span>
    <div class="d-flex gap-2">
        <button type="button" class="btn btn-outline-secondary" wire:click="closePanel">{{ __('Cancel') }}</button>
        <button type="submit" form="bulk-edit-form" class="btn btn-success" wire:loading.class="disabled" wire:target="applyChanges" @disabled(! in_array(true, $enabledFields, true))>{{ trans_choice('{1} Save :count booking|[2,*] Save :count bookings', $selectedCount, ['count' => $selectedCount]) }}</button>
    </div>
</div>

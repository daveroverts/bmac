<div class="modal-body">
    <p>{{ __('These slots are removed from the slot table. This cannot be undone.') }}</p>
    <ul class="list-group mb-3">
        @foreach ([__('Booked') => $bookedCount, __('Reserved') => $reservedCount, __('Open') => $openCount] as $statusLabel => $statusCount)
            @if ($statusCount > 0)
                <li class="list-group-item d-flex justify-content-between"><span>{{ $statusLabel }}</span><strong>{{ $statusCount }}</strong></li>
            @endif
        @endforeach
    </ul>
    <p>
        @if ($affectedPilotCount > 0)
            {{ trans_choice('{1} :count pilot gets a "booking deleted" email.|[2,*] :count pilots get a "booking deleted" email.', $affectedPilotCount, ['count' => $affectedPilotCount]) }}
        @else
            {{ __('No pilots are affected.') }}
        @endif
    </p>
    <details>
        <summary class="small">{{ __('Show the selected callsigns') }}</summary>
        <p class="small font-monospace mt-2 mb-0">{{ $selectedBookings->map(fn ($booking) => $booking->formatted_callsign)->implode(' · ') }}</p>
    </details>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-outline-secondary" data-autofocus wire:click="closePanel">{{ __('Cancel') }}</button>
    <button type="button" class="btn btn-danger" wire:click="deleteSelected" wire:loading.class="disabled" wire:target="deleteSelected">
        <i class="fas fa-trash"></i> {{ trans_choice('{1} Delete :count booking|[2,*] Delete :count bookings', $selectedCount, ['count' => $selectedCount]) }}
    </button>
</div>

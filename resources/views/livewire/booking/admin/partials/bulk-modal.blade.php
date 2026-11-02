{{-- Bootstrap owns this element's classes, so Livewire only morphs its children. No fade: Bootstrap ignores show and hide during a transition, which desyncs it from Livewire. --}}
<div class="modal" id="bulk-action-modal" tabindex="-1" aria-labelledby="bulk-action-modal-title" aria-hidden="true"
    data-bs-backdrop="static" data-bs-keyboard="false" wire:ignore.self
    x-data="{ shownPanel: null }"
    x-effect="const modal = bootstrap.Modal.getOrCreateInstance($el); $wire.panel ? modal.show() : modal.hide()"
    x-on:show-bs-modal.dot="shownPanel = $wire.panel"
    x-on:shown-bs-modal.dot="$nextTick(() => $el.querySelector('[data-autofocus]')?.focus())"
    x-on:hidden-bs-modal.dot="const opener = document.getElementById('bulk-open-' + shownPanel); (opener && ! opener.disabled ? opener : document.getElementById('bulk-actions-bar'))?.focus(); shownPanel = null"
    x-on:keydown.escape="if ($wire.panel && ! $event.isComposing) { $wire.closePanel(); }">
    <div class="modal-dialog modal-lg modal-dialog-scrollable modal-fullscreen-sm-down">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title h5 text-body text-decoration-none" id="bulk-action-modal-title">
                    @if ($panel === 'edit')
                        {{ trans_choice('{1} Edit :count booking|[2,*] Edit :count bookings', $selectedCount, ['count' => $selectedCount]) }}
                    @elseif ($panel === 'email' && $emailTestMode)
                        {{ __('Send a test email to yourself') }}
                    @elseif ($panel === 'email')
                        {{ trans_choice('{1} Email :count pilot|[2,*] Email :count pilots', $affectedPilotCount, ['count' => $affectedPilotCount]) }}
                    @elseif ($panel === 'delete')
                        {{ trans_choice('{1} Delete :count booking?|[2,*] Delete :count bookings?', $selectedCount, ['count' => $selectedCount]) }}
                    @endif
                </h4>
                <button type="button" class="btn-close" aria-label="{{ __('Close') }}" wire:click="closePanel"></button>
            </div>

            @if ($panel === 'edit')
                @include('livewire.booking.admin.partials.edit-panel')
            @elseif ($panel === 'email')
                @include('livewire.booking.admin.partials.email-panel')
            @elseif ($panel === 'delete')
                @include('livewire.booking.admin.partials.delete-panel')
            @endif
        </div>
    </div>
</div>

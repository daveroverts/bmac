<div class="modal-body">
    <div role="status" aria-live="polite" aria-atomic="true">
        @if ($emailFeedback)
            <div @class(['alert', 'alert-danger' => $emailFailed, 'alert-success' => ! $emailFailed])>{{ $emailFeedback }}</div>
        @endif
    </div>
    @if ($reservedCount > 0)
        <p>{{ trans_choice('{1} Includes :count reserved booking.|[2,*] Includes :count reserved bookings.', $reservedCount, ['count' => $reservedCount]) }}</p>
    @endif
    <form id="bulk-email-form" wire:submit="sendEmail">
        <div class="mb-3">
            <label class="form-label" for="bulk-subject">{{ __('Subject') }}</label>
            <input id="bulk-subject" type="text" data-autofocus wire:model="emailSubject" @class(['form-control', 'is-invalid' => $errors->has('emailSubject')])>
            @error('emailSubject') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
        <div class="mb-3">
            <label class="form-label" for="bulk-message">{{ __('Message') }}</label>
            <textarea id="bulk-message" rows="6" aria-describedby="bulk-message-help" wire:model="emailMessage" @class(['form-control', 'is-invalid' => $errors->has('emailMessage')])></textarea>
            @error('emailMessage') <div class="invalid-feedback">{{ $message }}</div> @enderror
            <small id="bulk-message-help" class="form-text">{{ __('Salutation and closing are already included') }}</small>
        </div>
        <div class="form-check">
            <input id="bulk-test-mode" type="checkbox" class="form-check-input" wire:model.live="emailTestMode">
            <label class="form-check-label" for="bulk-test-mode">{{ __('Test mode: send only to me (:email)', ['email' => auth()->user()->email]) }}</label>
        </div>
    </form>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-outline-secondary" wire:click="closePanel">{{ __('Cancel') }}</button>
    <button type="submit" form="bulk-email-form" @class(['btn', 'btn-outline-success' => $emailTestMode, 'btn-success' => ! $emailTestMode]) wire:loading.class="disabled" wire:target="sendEmail">
        <i class="fa fa-envelope"></i>
        @if ($emailTestMode)
            {{ __('Send test email to myself') }}
        @else
            {{ trans_choice('{1} Send email to :count pilot|[2,*] Send email to :count pilots', $affectedPilotCount, ['count' => $affectedPilotCount]) }}
        @endif
    </button>
</div>

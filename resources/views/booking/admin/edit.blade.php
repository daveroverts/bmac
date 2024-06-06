@extends('layouts.app')

@section('content')
    <x-forms.alert />
    @include('layouts.alert')
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ $booking->event->name }} | {{ __('Edit Booking') }}</div>

                <div class="card-body">
                    <x-form :action="route('admin.bookings.update', $booking)" method="PATCH">
                        @bind($booking)
                        <x-form-group name="is_editable" :label="__('Editable?')" inline>
                            <x-form-radio name="is_editable" value="0" :label="__('No')" required />
                            <x-form-radio name="is_editable" value="1" :label="__('Yes')" required />
                            @slot('help')
                                <small class="form-text text-muted">
                                    {{ __('Choose if you want the booking to be editable (Callsign and Aircraft Code only) by users. This is useful when using \'import only\', but want to add extra slots') }}
                                </small>
                            @endslot
                        </x-form-group>

                        <x-form-input name="callsign" :label="__('Callsign')" maxlength="7" />
                        <x-form-input name="acType" :label="__('Aircraft code')" minlength="3" maxlength="4" />

                        @bind($flight)
                        
                        <x-form-group inline>
                            <x-form-input name="ctot" :bind="false" value="{{ $flight->ctot?->format('H:i') }}" type="time" :label="'<i class=\'fa fa-clock\'></i> ' . __('CTOT')">
                                @slot('append')
                                    z
                                @endslot
                            </x-form-input>
                            <x-form-input name="eta" :bind="false" value="{{ $flight->eta?->format('H:i') }}" type="time" :label="'<i class=\'fa fa-clock\'></i> ' . __('ETA')">
                                @slot('append')
                                    z
                                @endslot
                            </x-form-input>
                        </x-form-group>

                        <x-form-select name="dep" :label="__('Departure airport')" :options="$airports"
                            :placeholder="__('Choose...')" required />

                        <x-form-select name="arr" :label="__('Arrival airport')" :options="$airports"
                            :placeholder="__('Choose...')" required />

                        <x-form-group :label="__('PIC')">
                            {{ $booking->user ? $booking->user->pic : '-' }}
                        </x-form-group>

                        <x-form-textarea name="route" :label="__('Route')" />

                        @if ($booking->event->is_oceanic_event)
                            <x-form-input name="oceanicTrack" :label="__('Track')" maxlength="2" />
                        @endif

                        <x-form-input name="oceanicFL"
                            :label="$booking->event->is_oceanic_event ? __('Oceanic Entry Level') : __('Cruise FL')">
                            @slot('prepend')
                                FL
                            @endslot
                        </x-form-input>

                        <x-form-textarea name="notes" :label="__('Notes')" />
                        @endbind

                        @if ($booking->user_id)
                            <x-form-textarea name="message" :label="__('Message')" />

                            <x-form-group>
                                <x-form-checkbox name="notify_user" checked :label="__('Notify user?')" />
                            </x-form-group>
                        @endif

                        <x-form-submit>
                            <i class="fas fa-check"></i> {{ __('Update') }}
                        </x-form-submit>
                        @endbind
                    </x-form>
                </div>
            </div>
        </div>
    </div>
@endsection

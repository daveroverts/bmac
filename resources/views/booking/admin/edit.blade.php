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
                        <x-forms.form-group name="is_editable" :label="__('Editable?')" inline>
                            <x-forms.radio name="is_editable" value="0" :label="__('No')" inline required
                                           :should-be-checked="old('is_editable', $booking->is_editable) == 0" />
                            <x-forms.radio name="is_editable" value="1" :label="__('Yes')" inline required
                                           :should-be-checked="old('is_editable', $booking->is_editable) == 1" />
                            <x-slot:help>
                                {{ __('Choose if you want the booking to be editable (Callsign and Aircraft Code only) by users. This is useful when using \'import only\', but want to add extra slots') }}
                            </x-slot:help>
                        </x-forms.form-group>

                        <x-forms.input name="callsign" :label="__('Callsign')" maxlength="7" :value="$booking->callsign" />
                        <x-forms.input name="acType" :label="__('Aircraft code')" minlength="3" maxlength="4" :value="$booking->acType" />

                        <x-forms.form-group inline>
                            <x-forms.input name="ctot" type="time" :value="$flight->ctot?->format('H:i')" input-group-class="pr-2">
                                <x-slot:label>
                                    <i class="fa fa-clock"></i> CTOT
                                </x-slot:label>
                                <x-slot:append>
                                    z
                                </x-slot:append>
                            </x-forms.input>
                            <x-forms.input name="eta" type="time" :value="$flight->eta?->format('H:i')">
                                <x-slot:label>
                                    <i class="fa fa-clock"></i> ETA
                                </x-slot:label>
                                <x-slot:append>
                                    z
                                </x-slot:append>
                            </x-forms.input>
                        </x-forms.form-group>

                        <x-forms.select name="dep" :label="__('Departure airport')" :options="$airports" :placeholder="__('Choose...')" required
                            :value="$flight->dep" />

                        <x-forms.select name="arr" :label="__('Arrival airport')" :options="$airports" :placeholder="__('Choose...')" required
                            :value="$flight->arr" />

                        <x-forms.form-group name="pic" :label="__('PIC')">
                            {{ $booking->user ? $booking->user->pic : '-' }}
                        </x-forms.form-group>

                        <x-forms.textarea name="route" :label="__('Route')" :value="$flight->route" />

                        @if ($booking->event->is_oceanic_event)
                            <x-forms.input name="oceanicTrack" :label="__('Track')" maxlength="2" :value="$flight->oceanicTrack" />
                        @endif

                        <x-forms.input name="oceanicFL" :label="$booking->event->is_oceanic_event ? __('Oceanic Entry Level') : __('Cruise FL')" :value="$flight->oceanicFL">
                            <x-slot:prepend>
                                FL
                            </x-slot:prepend>
                        </x-forms.input>

                        <x-forms.textarea name="notes" :label="__('Notes')" :value="$flight->notes" />

                        @if ($booking->user_id)
                            <x-forms.textarea name="message" :label="__('Message')" />

                            <x-forms.form-group>
                                <x-forms.checkbox name="notify_user" :label="__('Notify user?')" />
                            </x-forms.form-group>
                        @endif

                        <x-forms.button type="submit">
                            <i class="fas fa-check"></i> {{ __('Update') }}
                        </x-forms.button>
                    </x-form>
                </div>
            </div>
        </div>
    </div>
@endsection

@extends('layouts.app')

@section('content')
    <x-forms.alert />
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ $event->name }} | {{ $bulk ? __('Add Timeslots') : __('Add Slot') }}</div>

                <div class="card-body">
                    <x-form :action="route('admin.bookings.store', $event)" method="POST">
                        <input type="hidden" name="id" value="{{ $event->id }}">
                        <input type="hidden" name="bulk" value="{{ $bulk ? 1 : 0 }}">

                        <x-forms.form-group name="event" :label="__('Event')">
                            {{ $event->name }} [{{ $event->startEvent->format('d-m-Y') }} |
                            {{ $event->startEvent->format('Hi') }}z -
                            {{ $event->endEvent->format('Hi') }}z]
                        </x-forms.form-group>

                        <x-forms.form-group name="is_callsign_editable" :label="__('Callsign editable?')" inline>
                            <x-forms.radio name="is_callsign_editable" value="0" :label="__('No')" inline required :should-be-checked="old('is_callsign_editable') == 0" />
                            <x-forms.radio name="is_callsign_editable" value="1" :label="__('Yes')" inline required :should-be-checked="old('is_callsign_editable') == 1" />
                            <x-slot:help>
                                {{ __('Choose if you want the Callsign to be editable by the users. This can be useful if you imported aircraft types only, and want the user to fly under their own callsign') }}
                            </x-slot:help>
                        </x-forms.form-group>

                        <x-forms.form-group name="is_actype_editable" :label="__('Aircraft code editable?')" inline>
                            <x-forms.radio name="is_actype_editable" value="0" :label="__('No')" inline required :should-be-checked="old('is_actype_editable') == 0" />
                            <x-forms.radio name="is_actype_editable" value="1" :label="__('Yes')" inline required :should-be-checked="old('is_actype_editable') == 1" />
                            <x-slot:help>
                                {{ __('Choose if you want the Aircraft Code to be editable by the users. This can be useful if you imported callsigns only, and want the user to choose an aircraft of their choice') }}
                            </x-slot:help>
                        </x-forms.form-group>

                        @if (!$bulk)
                            <x-forms.input name="callsign" :label="__('Callsign')" maxlength="7" />
                            <x-forms.input name="acType" :label="__('Aircraft code')" minlength="3" maxlength="4" />
                        @endif

                        <x-forms.airport-select name="dep" :label="__('Departure airport')" :placeholder="__('Search by name or code...')" required
                            :value="$event->dep" />

                        <x-forms.airport-select name="arr" :label="__('Arrival airport')" :placeholder="__('Search by name or code...')" required
                            :value="$event->dep" />

                        @if ($bulk)
                            <x-forms.form-group inline>
                                <x-forms.input name="start" type="time" :value="old('start', $event->startEvent->format('H:i'))">
                                    <x-slot:label>
                                        <i class="fa fa-clock"></i> Start
                                    </x-slot:label>
                                    <x-slot:append>
                                        z
                                    </x-slot:append>
                                </x-forms.input>
                                <x-forms.input name="end" type="time" :value="old('end', $event->endEvent->format('H:i'))">
                                    <x-slot:label>
                                        <i class="fa fa-clock"></i> End
                                    </x-slot:label>
                                    <x-slot:append>
                                        z
                                    </x-slot:append>
                                </x-forms.input>

                                <x-forms.input name="separation" type="number" :label="__('Separation (in minutes)')" />
                            </x-forms.form-group>
                        @else
                            <x-forms.form-group inline>
                                <x-forms.input name="ctot" type="time" input-group-class="pe-2">
                                    <x-slot:label>
                                        <i class="fa fa-clock"></i> CTOT
                                    </x-slot:label>
                                    <x-slot:append>
                                        z
                                    </x-slot:append>
                                </x-forms.input>
                                <x-forms.input name="eta" type="time">
                                    <x-slot:label>
                                        <i class="fa fa-clock"></i> ETA
                                    </x-slot:label>
                                    <x-slot:append>
                                        z
                                    </x-slot:append>
                                </x-forms.input>
                            </x-forms.form-group>

                            <x-forms.textarea name="route" :label="__('Route')" />

                            <x-forms.input name="oceanicFL" :label="$event->is_oceanic_event ? __('Oceanic Entry Level') : __('Cruise FL')">
                                <x-slot:prepend>
                                    FL
                                </x-slot:prepend>
                            </x-forms.input>
                        @endif

                        <x-forms.textarea name="notes" :label="__('Notes')" />

                        <x-forms.button type="submit">
                            @if ($bulk)
                                <i class="fa fa-plus"></i> {{ __('Add timeslots') }}
                            @else
                                <i class="fa fa-plus"></i> {{ __('Add slot') }}
                            @endif
                        </x-forms.button>
                    </x-form>
                </div>
            </div>
        </div>
    </div>
@endsection

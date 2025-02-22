@extends('layouts.app')

@section('content')
    <x-forms.alert />
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ $event->id ? 'Edit' : 'Add new' }} Event</div>

                <div class="card-body">
                    <x-form :action="$event->id ? route('admin.events.update', $event) : route('admin.events.store')" :method="$event->id ? 'PATCH' : 'POST'">
                        <x-forms.form-group name="is_online" :label="__('Show online?')" inline>
                            <x-forms.radio name="is_online" value="0" :label="__('No')" inline required
                                :checked="!$event->is_online" />
                            <x-forms.radio name="is_online" value="1" :label="__('Yes')" inline required
                                :checked="$event->is_online" />
                            <x-slot:help>
                                {{ __("Choose here if you want the event to be reachable by it's generated url") }}
                            </x-slot:help>
                        </x-forms.form-group>

                        <x-forms.form-group name="show_on_homepage" :label="__('Show on homepage?')" inline>
                            <x-forms.radio name="show_on_homepage" value="0" :label="__('No')" inline required
                                :checked="!$event->show_on_homepage" />
                            <x-forms.radio name="show_on_homepage" value="1" :label="__('Yes')" inline required
                                :checked="$event->show_on_homepage" />
                            <x-slot:help>
                                {{ __("Choose here if you want to show the event on the homepage. If turned off, the event can only be reached by the url. NOTE: If 'Show Online' is off, the event won't be shown at all") }}
                            </x-slot:help>
                        </x-forms.form-group>

                        <x-forms.input name="name" :label="__('Name')" required :value="$event->name" />

                        <x-forms.select name="event_type_id" :label="__('Event type')" :value="$event->event_type_id" :options="$eventTypes"
                            :placeholder="__('Choose...')" required />

                        <x-forms.form-group name="import_only" :label="__('Only import?')" inline>
                            <x-forms.radio name="import_only" value="0" :label="__('No')" inline required
                                :checked="!$event->import_only" />
                            <x-forms.radio name="import_only" value="1" :label="__('Yes')" inline required
                                :checked="$event->import_only" />
                            <x-slot:help>
                                {{ __('If enabled, only admins can fill in details via import script') }}
                            </x-slot:help>
                        </x-forms.form-group>

                        <x-forms.form-group name="uses_times" :label="__('Show times?')" inline>
                            <x-forms.radio name="uses_times" value="0" :label="__('No')" inline required
                                :checked="!$event->uses_times" />
                            <x-forms.radio name="uses_times" value="1" :label="__('Yes')" inline required
                                :checked="$event->uses_times" />
                            <x-slot:help>
                                {{ __('If enabled, CTOT and ETA (if set in booking) will be shown') }}
                            </x-slot:help>
                        </x-forms.form-group>

                        <x-forms.form-group name="multiple_bookings_allowed" :label="__('Multiple bookings allowed?')" inline>
                            <x-forms.radio name="multiple_bookings_allowed" value="0" :label="__('No')" inline
                                required :checked="!$event->multiple_bookings_allowed" />
                            <x-forms.radio name="multiple_bookings_allowed" value="1" :label="__('Yes')" inline
                                required :checked="$event->multiple_bookings_allowed" />
                            <x-slot:help>
                                {{ __('If enabled, a user is allowed to book multiple flights for this event') }}
                            </x-slot:help>
                        </x-forms.form-group>

                        <x-forms.form-group name="is_oceanic_event" :label="__('Oceanic event?')" inline>
                            <x-forms.radio name="is_oceanic_event" value="0" :label="__('No')" inline required
                                :checked="!$event->is_oceanic_event" />
                            <x-forms.radio name="is_oceanic_event" value="1" :label="__('Yes')" inline required
                                :checked="$event->is_oceanic_event" />
                            <x-slot:help>
                                {{ __('If enabled, users can fill in a SELCAL code') }}
                            </x-slot:help>

                            <x-error field="is_oceanic_event" class="invalid-feedback" />
                        </x-forms.form-group>

                        <x-forms.select name="dep" :label="__('Departure airport')" :value="$event->dep" :options="$airports"
                            :placeholder="__('Choose...')" required />

                        <x-forms.select name="arr" :label="__('Arrival airport')" :value="$event->arr" :options="$airports"
                            :placeholder="__('Choose...')" required />

                        <x-forms.form-group name="startEvent" :label="__('Start event (UTC)')">
                            <x-flat-pickr name="startEvent" value="{{ old('startEvent', $event->startEvent) }}" />
                        </x-forms.form-group>

                        <x-forms.form-group name="endEvent" :label="__('End event (UTC)')">
                            <x-flat-pickr name="endEvent" value="{{ old('endEvent', $event->endEvent) }}" />
                        </x-forms.form-group>

                        <x-forms.form-group name="startBooking" :label="__('Start booking (UTC)')">
                            <x-flat-pickr name="startBooking" value="{{ old('startBooking', $event->startBooking) }}" />
                        </x-forms.form-group>

                        <x-forms.form-group name="endBooking" :label="__('End booking (UTC)')">
                            <x-flat-pickr name="endBooking" value="{{ old('endBooking', $event->endBooking) }}" />
                        </x-forms.form-group>

                        <x-forms.input name="image_url" :label="__('Image URL')" placeholder="https://example.org" />

                        <x-forms.textarea name="description" :label="__('Description')" :value="$event->description" tinymce />

                        <x-forms.button type="submit">
                            @if ($event->id)
                                <i class="fa fa-check"></i> {{ __('Edit') }}
                            @else
                                <i class="fa fa-plus"></i> {{ __('Add') }}
                            @endif
                            </x-forms.button>
                    </x-form>
                </div>
            </div>
        </div>
    </div>
@endsection

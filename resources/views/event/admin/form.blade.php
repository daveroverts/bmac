@extends('layouts.app')

@section('content')
    <x-forms.alert />
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ $event->id ? 'Edit' : 'Add new' }} Event</div>

                <div class="card-body">
                    <x-form :action="$event->id ? route('admin.events.update', $event) : route('admin.events.store')"
                        :method="$event->id ? 'PATCH' : 'POST'">
                        @bind($event)

                        <x-form-group name="is_online" :label="__('Show online?')" inline>
                            <x-form-radio name="is_online" value="0" :label="__('No')" required />
                            <x-form-radio name="is_online" value="1" :label="__('Yes')" required />
                            @slot('help')
                                <small class="form-text text-muted">
                                    {{ __("Choose here if you want the event to be reachable by it's generated url") }}
                                </small>
                            @endslot
                        </x-form-group>

                        <x-form-group name="show_on_homepage" :label="__('Show on homepage?')" inline>
                            <x-form-radio name="show_on_homepage" value="0" :label="__('No')" required />
                            <x-form-radio name="show_on_homepage" value="1" :label="__('Yes')" required />
                            @slot('help')
                                <small class="form-text text-muted">
                                    {{ __("Choose here if you want to show the event on the homepage. If turned off, the event can only be reached by the url. NOTE: If 'Show Online' is off, the event won't be shown at all") }}
                                </small>
                            @endslot
                        </x-form-group>

                        <x-form-input name="name" :label="__('Name')" required />

                        <x-form-select name="event_type_id" :label="__('Event type')" :options="$eventTypes"
                            :placeholder="__('Choose...')" required />

                        <x-form-group name="import_only" :label="__('Only import?')" inline>
                            <x-form-radio name="import_only" value="0" :label="__('No')" required />
                            <x-form-radio name="import_only" value="1" :label="__('Yes')" required />
                            @slot('help')
                                <small class="form-text text-muted">
                                    {{ __('If enabled, only admins can fill in details via import script') }}
                                </small>
                            @endslot
                        </x-form-group>

                        <x-form-group name="uses_times" :label="__('Show times?')" inline>
                            <x-form-radio name="uses_times" value="0" :label="__('No')" required />
                            <x-form-radio name="uses_times" value="1" :label="__('Yes')" required />
                            @slot('help')
                                <small class="form-text text-muted">
                                    {{ __('If enabled, CTOT and ETA (if set in booking) will be shown') }}
                                </small>
                            @endslot
                        </x-form-group>

                        <x-form-group name="multiple_bookings_allowed" :label="__('Multiple bookings allowed?')" inline>
                            <x-form-radio name="multiple_bookings_allowed" value="0" :label="__('No')" required />
                            <x-form-radio name="multiple_bookings_allowed" value="1" :label="__('Yes')" required />
                            @slot('help')
                                <small class="form-text text-muted">
                                    {{ __('If enabled, a user is allowed to book multiple flights for this event') }}
                                </small>
                            @endslot
                        </x-form-group>

                        <x-form-group name="is_oceanic_event" :label="__('Oceanic event?')" inline>
                            <x-form-radio name="is_oceanic_event" value="0" :label="__('No')" required />
                            <x-form-radio name="is_oceanic_event" value="1" :label="__('Yes')" required />
                            @slot('help')
                                <small class="form-text text-muted">
                                    {{ __('If enabled, users can fill in a SELCAL code') }}
                                </small>
                            @endslot
                        </x-form-group>

                        <x-form-select name="dep" :label="__('Departure airport')" :options="$airports"
                            :placeholder="__('Choose...')" required />

                        <x-form-select name="arr" :label="__('Arrival airport')" :options="$airports"
                            :placeholder="__('Choose...')" required />

                        <x-form-group :label="__('Start event (UTC)')">
                            <x-flat-pickr name="startEvent" value="{{ old('startEvent', $event->startEvent) }}" />
                        </x-form-group>

                        <x-form-group :label="__('End event (UTC)')">
                            <x-flat-pickr name="endEvent" value="{{ old('endEvent', $event->endEvent) }}" />
                        </x-form-group>

                        <x-form-group :label="__('Start booking (UTC)')">
                            <x-flat-pickr name="startBooking" value="{{ old('startBooking', $event->startBooking) }}" />
                        </x-form-group>

                        <x-form-group :label="__('End booking (UTC)')">
                            <x-flat-pickr name="endBooking" value="{{ old('endBooking', $event->endBooking) }}" />
                        </x-form-group>

                        <x-form-group :label="__('Start Confirming (UTC)')">
                            <x-flat-pickr name="startConfirm" value="{{ old('startConfirm', $event->startConfirm) }}" />
                        </x-form-group>

                        <x-form-group :label="__('End Confirming (UTC)')">
                            <x-flat-pickr name="endConfirm" value="{{ old('endConfirm', $event->endConfirm) }}" />
                        </x-form-group>

                        <x-form-input name="image_url" :label="__('Image URL')" placeholder="https://example.org" />

                        <x-form-textarea name="description" :label="__('Description')" class="tinymce" />

                        <x-form-submit>
                            @if ($event->id)
                                <i class="fa fa-check"></i> {{ __('Edit') }}
                            @else
                                <i class="fa fa-plus"></i> {{ __('Add') }}
                            @endif
                        </x-form-submit>

                        @endbind
                    </x-form>
                </div>
            </div>
        </div>
    </div>
@endsection

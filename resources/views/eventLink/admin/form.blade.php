@extends('layouts.app')

@section('content')
    <x-forms.alert />
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ $eventLink->id ? 'Edit' : 'Add new' }} Event Link</div>

                <div class="card-body">
                    <x-form :action="$eventLink->id
                        ? route('admin.eventLinks.update', $eventLink)
                        : route('admin.eventLinks.store')" :method="$eventLink->id ? 'PATCH' : 'POST'">

                        <x-forms.select name="event_link_type_id" :label="__('Type')" :options="$eventLinkTypes" :placeholder="__('Choose...')"
                            required :value="$eventLink->event_link_type_id" />
                        @if ($eventLink->id)
                            <x-forms.form-group name="event_id" :label="__('Event')">
                                {{ $eventLink->event->name }} [{{ $eventLink->event->startEvent->format('d-m-Y') }}]
                            </x-forms.form-group>
                        @else
                            <x-forms.select name="event_id" :label="__('Event')" :options="$events" :placeholder="__('Choose...')" required />
                        @endif
                        <x-forms.input name="name" :label="__('Name')" :value="$eventLink->name" :help="__('Leave empty to use the type as name')" :value="old('name', $eventLink->name)" />
                        <x-forms.input name="url" :label="__('URL')" :value="$eventLink->url" placeholder="https://example.org" required :value="old('name', $eventLink->url)" />

                        <x-forms.button type="submit">
                            @if ($eventLink->id)
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

@extends('layouts.app')

@section('content')
    <x-forms.alert />
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ $eventLink->id ? 'Edit' : 'Add new' }} Event Link</div>

                <div class="card-body">
                    <x-form
                        :action="$eventLink->id ? route('admin.eventLinks.update', $eventLink) : route('admin.eventLinks.store')"
                        :method="$eventLink->id ? 'PATCH' : 'POST'">

                        @bind($eventLink)
                        <x-form-select name="event_link_type_id" :label="__('Type')" :options="$eventLinkTypes"
                            :placeholder="__('Choose...')" required />
                        @if ($eventLink->id)
                            <x-form-group :label="__('Event')">
                                {{ $eventLink->event->name }} [{{ $eventLink->event->startEvent->format('d-m-Y') }}]
                            </x-form-group>
                        @else
                            <x-form-select name="event_id" :label="__('Event')" :options="$events"
                                :placeholder="__('Choose...')" required />
                        @endif
                        <x-form-input name="name" :label="__('Name')" />
                        <x-form-input name="url" :label="__('URL')" placeholder="https://example.org" required />

                        <x-form-submit>
                            @if ($eventLink->id)
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

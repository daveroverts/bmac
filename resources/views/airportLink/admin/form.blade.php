@extends('layouts.app')

@section('content')
    <x-forms.alert />
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ $airportLink->id ? __('Edit') : __('Add new') }} {{ __('Airport Link') }}
                </div>

                <div class="card-body">
                    <x-form
                        :action="$airportLink->id ? route('admin.airportLinks.update', $airportLink) : route('admin.airportLinks.store')"
                        :method="$airportLink->id ? 'PATCH' : 'POST'">

                        @bind($airportLink)
                        <x-form-select name="airportLinkType_id" :label="__('Type')" :options="$airportLinkTypes"
                            :placeholder="__('Choose...')" required />
                        @if ($airportLink->id)
                            <x-form-group :label="__('Airport')">
                                {{ $airportLink->airport->icao . '[' . $airportLink->airport->name . ' (' . $airportLink->airport->iata . ')]' }}
                            </x-form-group>
                        @else
                            <x-form-select name="airport_id" :label="__('Airport')" :options="$airports"
                                :placeholder="__('Choose...')" required />
                        @endif
                        <x-form-input name="name" :label="__('Name')" />
                        <x-form-input name="url" :label="__('URL')" placeholder="https://example.org" required />

                        <x-form-submit>
                            @if ($airportLink->id)
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

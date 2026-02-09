@extends('layouts.app')

@section('content')
    <x-forms.alert />
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ $airportLink->id ? __('Edit') : __('Add new') }} {{ __('Airport Link') }}
                </div>

                <div class="card-body">
                    <x-form :action="$airportLink->id
                        ? route('admin.airportLinks.update', $airportLink)
                        : route('admin.airportLinks.store')" :method="$airportLink->id ? 'PATCH' : 'POST'">

                        <x-forms.select name="airportLinkType_id" :label="__('Type')" :options="$airportLinkTypes" :placeholder="__('Choose...')"
                            required :value="$airportLink->airportLinkType_id" />
                        @if ($airportLink->id)
                            <x-forms.form-group name="airport_id" label="Airport">
                                {{ $airportLink->airport->icao }} [{{ $airportLink->airport->name }}
                                ({{ $airportLink->airport->iata }})]
                            </x-forms.form-group>
                        @else
                            <x-forms.select name="airport_id" :label="__('Airport')" :options="$airports" :placeholder="__('Choose...')"
                                required />
                        @endif
                        <x-forms.input name="name" :label="__('Name')" :help="__('Leave empty to use the type as name')" :value="old('name', $airportLink->name)" />
                        <x-forms.input name="url" :label="__('URL')" placeholder="https://example.org" required :value="old('url', $airportLink->url)" />

                        <x-forms.button type="submit">
                            @if ($airportLink->id)
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

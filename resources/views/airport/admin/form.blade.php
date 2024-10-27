@extends('layouts.app')

@section('content')
    <x-forms.alert />
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ $airport->id ? __('Edit') : __('Add new') }} {{ __('Airport') }}</div>

                <div class="card-body">
                    <x-form :action="$airport->id ? route('admin.airports.update', $airport) : route('admin.airports.store')" :method="$airport->id ? 'PATCH' : 'POST'">

                        <x-forms.input name="icao" :label="__('ICAO')" :value="$airport->icao" required maxlength="4" />
                        <x-forms.input name="iata" :label="__('IATA')" :value="$airport->iata" required maxlength="3" />
                        <x-forms.input name="name" :label="__('Name')" :value="$airport->name" required />
                        <x-forms.input name="latitude" :value="$airport->latitude" />
                        <x-forms.input name="longitude" :label="__('Longitude')" :value="$airport->longitude" />

                        <x-forms.button type="submit">
                            @if ($airport->id)
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

@extends('layouts.app')

@section('content')
    <x-forms.alert />
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ $airport->id ? __('Edit') : __('Add new') }} {{ __('Airport') }}</div>

                <div class="card-body">
                    <x-form
                        :action="$airport->id ? route('admin.airports.update', $airport) : route('admin.airports.store')"
                        :method="$airport->id ? 'PATCH' : 'POST'">

                        @bind($airport)
                        <x-form-input name="icao" :label="__('ICAO')" required maxlength="4" />
                        <x-form-input name="iata" :label="__('IATA')" required maxlength="3" />
                        <x-form-input name="name" :label="__('Name')" required />
                        <x-form-input name="latitude" :label="__('Latitude')" />
                        <x-form-input name="longitude" :label="__('Longitude')" />

                        <x-form-submit>
                            @if ($airport->id)
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

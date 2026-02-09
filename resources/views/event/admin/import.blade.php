@extends('layouts.app')

@section('content')
    <x-forms.alert />
    @include('layouts.alert')
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ $event->name }} | {{ __('Import') }}</div>

                <div class="card-body">
                    <x-form :action="route('admin.bookings.import', $event)" method="POST" enctype="multipart/form-data">
                        <x-forms.input name="file" type="file" :label="__('File')" />

                        <x-forms.form-group :label="__('Headers in <strong>bold</strong> are mandatory')">
                            @if ($event->event_type_id == \App\Enums\EventType::MULTIFLIGHTS->value)
                                <strong><abbr title="[hh:mm]">CTOT 1</abbr></strong> - <strong><abbr title="[ICAO]">Airport
                                        1</abbr></strong> -
                                <strong><abbr title="[hh:mm]">CTOT 2</abbr></strong> - <strong><abbr title="[ICAO]">Airport
                                        2</abbr></strong> -
                                <strong><abbr title="[ICAO]">Airport 3</abbr></strong>
                            @else
                                Call Sign | <strong><abbr title="[ICAO]">Origin</abbr></strong> |
                                <strong><abbr title="[ICAO]">Destination</abbr></strong> |
                                <abbr title="[hh:mm]">CTOT</abbr> | <abbr title="[hh:mm]">ETA</abbr> |
                                <abbr title="[ICAO]">Aircraft Type</abbr> | Route | Notes | Track | <abbr
                                    title="Max 3 numbers. Examples: 370">FL</abbr>
                            @endif
                        </x-forms.form-group>

                        <x-forms.form-group inline>
                            <x-forms.button type="submit">
                                <i class="fas fa-check"></i> Import
                            </x-forms.button>

                            <a class="btn btn-secondary"
                                href="{{ $event->event_type_id == \App\Enums\EventType::MULTIFLIGHTS->value ? url('import_multi_flights_template.xlsx') : url('import_template.xlsx') }}">
                                <i class="fas fa-file-excel"></i> Download template
                            </a>
                        </x-forms.form-group>
                    </x-form>
                </div>
            </div>
        </div>
    </div>
@endsection

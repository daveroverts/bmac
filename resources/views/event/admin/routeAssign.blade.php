@extends('layouts.app')

@section('content')
    <x-forms.alert />
    @include('layouts.alert')
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ $event->name }} | {{ __('Import') }}</div>

                <div class="card-body">
                    <x-form :action="route('admin.bookings.routeAssign', $event)" method="POST" enctype="multipart/form-data">
                        <x-form-input name="file" type="file" :label="__('File')" />

                        <x-form-group :label="__('Headers in <strong>bold</strong> are mandatory')">
                            <strong><abbr title="[ICAO]">From</abbr></strong> |
                            <strong><abbr title="[ICAO]">To</abbr></strong> |
                            <strong>Route</strong> |
                            Notes
                        </x-form-group>

                        <form-group inline>
                            <x-form-submit>
                                <i class="fas fa-check"></i> Auto-Assign Routes
                            </x-form-submit>
                            <a class="btn btn-secondary"
                                href="{{ url('import_multi_flights_assign_routes_template.xlsx') }}">
                                <i class="fas fa-file-excel"></i> Download template
                            </a>
                        </form-group>
                    </x-form>
                </div>
            </div>
        </div>
    </div>
@endsection

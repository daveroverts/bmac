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
                        <x-form-input name="file" type="file" :label="__('File')" />

                        <x-form-submit>
                            <i class="fas fa-check"></i> Auto-Assign Routes
                        </x-form-submit>
                    </x-form>
                </div>
            </div>
        </div>
    </div>
@endsection

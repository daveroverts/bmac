@extends('layouts.app')

@section('content')
    <x-forms.alert />
    @include('layouts.alert')
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ $event->name }} | {{ __('Auto-Assign FL / Route') }}</div>

                <div class="card-body">
                    <x-form :action="route('admin.bookings.autoAssign', $event)" method="POST">
                        <x-form-input name="oceanicTrack1" :label="__('Track #:number', ['number' => 1])" required
                            maxlength="2" />
                        <x-form-textarea name="route1" :label="__('Route #:number', ['number' => 1])" />

                        <x-form-input name="oceanicTrack2" :label="__('Track #:number', ['number' => 2])" required
                            maxlength="2" />
                        <x-form-textarea name="route2" :label="__('Route #:number', ['number' => 2])" />

                        <x-form-group inline>
                            <x-form-input name="minFL" :label="__('Minimum Oceanic Entry FL')" default="320" required
                                minlength="3" maxlength="3" />

                            <x-form-input name="maxFL" :label="__('Maximum Oceanic Entry FL')" default="380" required
                                minlength="3" maxlength="3" />
                        </x-form-group>

                        <x-form-group>
                            <x-form-checkbox name="checkAssignAllFlights" :label="__('Auto-assign all flights?')">
                                @slot('help')
                                    <small class="form-text text-muted">
                                        {{ __('When enabled, all flights, regardless of being booked will be auto-assigned') }}
                                    </small>
                                @endslot
                            </x-form-checkbox>
                        </x-form-group>

                        <x-form-submit>
                            <i class="fas fa-check"></i> {{ __('Auto-Assign FL / Route') }}
                        </x-form-submit>

                    </x-form>
                </div>
            </div>
        </div>
    </div>
@endsection

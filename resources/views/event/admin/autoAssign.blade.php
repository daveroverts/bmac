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
                        <x-forms.input name="oceanicTrack1" :label="__('Track #:number', ['number' => 1])" required maxlength="2" />
                        <x-forms.textarea name="route1" :label="__('Route #:number', ['number' => 1])" />

                        <x-forms.input name="oceanicTrack2" :label="__('Track #:number', ['number' => 2])" required maxlength="2" />
                        <x-forms.textarea name="route2" :label="__('Route #:number', ['number' => 2])" />

                        <x-forms.form-group inline>
                            <x-forms.input name="minFL" :label="__('Minimum Oceanic Entry FL')" default="320" required minlength="3"
                                maxlength="3">
                                @slot('prepend')
                                    FL
                                @endslot
                            </x-forms.input>

                            <x-forms.input name="maxFL" :label="__('Maximum Oceanic Entry FL')" default="380" required minlength="3"
                                maxlength="3">
                                @slot('prepend')
                                    FL
                                @endslot
                            </x-forms.input>
                        </x-forms.form-group>

                        <x-forms.form-group :help="__('When enabled, all flights, regardless of being booked will be auto-assigned')">
                            <x-forms.checkbox name="checkAssignAllFlights" :label="__('Auto-assign all flights?')" />
                        </x-forms.form-group>

                        <x-forms.button type="submit">
                            <i class="fas fa-check"></i> {{ __('Auto-Assign FL / Route') }}
                        </x-forms.button>

                    </x-form>
                </div>
            </div>
        </div>
    </div>
@endsection

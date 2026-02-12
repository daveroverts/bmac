@extends('layouts.app')

@section('content')
    <x-forms.alert />
    @include('layouts.alert')
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">My settings</div>

                <div class="card-body">
                    <x-form :action="route('user.saveSettings')" method="PATCH">
                            <x-forms.form-group name="airport_view" :label="__('Default airport view')">
                                <x-forms.radio name="airport_view" value="0" :label="__('Name') . ': Amsterdam Airport Schiphol - EHAM | [AMS]'" required
                                               :should-be-checked="old('airport_view', $user->airport_view->value) == 0"/>
                                <x-forms.radio name="airport_view" value="1" :label="__('ICAO') . ': EHAM - Amsterdam Airport Schiphol | [AMS]'" required
                                               :should-be-checked="old('airport_view', $user->airport_view->value) == 1"/>
                                <x-forms.radio name="airport_view" value="2" :label="__('IATA') . ': AMS - Amsterdam Airport Schiphol | [EHAM]'" required
                                               :should-be-checked="old('airport_view', $user->airport_view->value) == 2"/>
                            </x-forms.form-group>

                            <x-forms.form-group name="use_monospace_font" :label="__('Use monospace font')" inline>
                                <x-forms.radio name="use_monospace_font" value="0" :label="__('No')" inline required
                                               :should-be-checked="old('use_monospace_font', $user->use_monospace_font) == 0"/>
                                <x-forms.radio name="use_monospace_font" value="1" :label="__('Yes')" inline required
                                               :should-be-checked="old('use_monospace_font', $user->use_monospace_font) == 1"/>
                            </x-forms.form-group>

                        <x-forms.button type="submit">
                            <i class="fas fa-save"></i> {{ __('Save settings') }}
                        </x-forms.button>
                    </x-form>
                </div>
            </div>
        </div>
    </div>
@endsection

@extends('layouts.app')

@section('content')
    @if (count($errors) > 0)
        <div class="alert alert-dismissible alert-danger">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    @include('layouts.alert')
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">My settings</div>

                <div class="card-body">
                    <x-form :action="route('user.saveSettings')" method="PATCH">
                        @bind($user)
                        <x-form-group name="airport_view" :label="__('Default airport view')">
                            <x-form-radio name="airport_view" value="0"
                                :label="__('Name') . ': Amsterdam Airport Schiphol - EHAM | [AMS]'" required />
                            <x-form-radio name="airport_view" value="1"
                                :label="__('ICAO') . ': EHAM - Amsterdam Airport Schiphol | [AMS]'" required />
                            <x-form-radio name="airport_view" value="2"
                                :label="__('IATA') . ': AMS - Amsterdam Airport Schiphol | [EHAM]'" required />
                        </x-form-group>

                        <x-form-group name="use_monospace_font" :label="__('Use monospace font')" inline>
                            <x-form-radio name="use_monospace_font" value="0" :label="__('No')" required />
                            <x-form-radio name="use_monospace_font" value="1" :label="__('Yes')" required />
                        </x-form-group>

                        <x-form-submit>
                            <i class="fas fa-save"></i> Save settings
                        </x-form-submit>
                        @endbind
                    </x-form>
                </div>
            </div>
        </div>
    </div>
@endsection

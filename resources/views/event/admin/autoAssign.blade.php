@extends('layouts.app')

@section('content')
    <x-forms.alert />
    @include('layouts.alert')
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ $event->name }} | Auto-Assign FL / Route</div>

                <div class="card-body">
                    <form method="POST" action="{{ route('admin.bookings.autoAssign', $event) }}">
                        @csrf
                        @method('PATCH')

                        {{-- Track #1 --}}
                        <div class="form-group row">
                            <label for="oceanicTrack1" class="col-md-4 col-form-label text-md-right">Track #1</label>

                            <div class="col-md-6">
                                <input id="oceanicTrack1" type="text"
                                    class="form-control{{ $errors->has('oceanicTrack1') ? ' is-invalid' : '' }}"
                                    name="oceanicTrack1" value="{{ old('oceanicTrack') }}" max="2">

                                @if ($errors->has('oceanicTrack'))
                                    <span class="invalid-feedback">
                                        <strong>{{ $errors->first('oceanicTrack1') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        {{-- Route #1 --}}
                        <div class="form-group row">
                            <label for="route1" class="col-md-4 col-form-label text-md-right">Route #1</label>

                            <div class="col-md-6">
                                <textarea class="form-control" id="route1" name="route1">{{ old('route1') }}</textarea>

                                @if ($errors->has('route'))
                                    <span class="invalid-feedback">
                                        <strong>{{ $errors->first('route1') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        {{-- Track #2 --}}
                        <div class="form-group row">
                            <label for="oceanicTrack2" class="col-md-4 col-form-label text-md-right">Track #2</label>

                            <div class="col-md-6">
                                <input id="oceanicTrack2" type="text"
                                    class="form-control{{ $errors->has('oceanicTrack1') ? ' is-invalid' : '' }}"
                                    name="oceanicTrack2" value="{{ old('oceanicTrack2') }}" max="2">

                                @if ($errors->has('oceanicTrack'))
                                    <span class="invalid-feedback">
                                        <strong>{{ $errors->first('oceanicTrack2') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        {{-- Route #2 --}}
                        <div class="form-group row">
                            <label for="route2" class="col-md-4 col-form-label text-md-right">Route #2</label>

                            <div class="col-md-6">
                                <textarea class="form-control" id="route1" name="route2">{{ old('route2') }}</textarea>

                                @if ($errors->has('route2'))
                                    <span class="invalid-feedback">
                                        <strong>{{ $errors->first('route2') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        {{-- Max FL --}}
                        <div class="form-group row">
                            <label for="minFL" class="col-md-4 col-form-label text-md-right">Minimum Oceanic Entry
                                FL</label>

                            <div class="col-md-6">
                                <input id="minFL" type="text"
                                    class="form-control{{ $errors->has('minFL') ? ' is-invalid' : '' }}" name="minFL"
                                    value="{{ old('minFL', 320) }}" max="3">

                                @if ($errors->has('minFL'))
                                    <span class="invalid-feedback">
                                        <strong>{{ $errors->first('minFL') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        {{-- Max FL --}}
                        <div class="form-group row">
                            <label for="maxFL" class="col-md-4 col-form-label text-md-right">Maximum Oceanic Entry
                                FL</label>

                            <div class="col-md-6">
                                <input id="maxFL" type="text"
                                    class="form-control{{ $errors->has('maxFL') ? ' is-invalid' : '' }}" name="maxFL"
                                    value="{{ old('maxFL', 380) }}" max="3">

                                @if ($errors->has('minFL'))
                                    <span class="invalid-feedback">
                                        <strong>{{ $errors->first('maxFL') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        {{-- Assign All Flights --}}
                        <div class="form-group row">
                            <div class="col-md-6 offset-md-4">
                                <div class="custom-control custom-checkbox">
                                    <input class="custom-control-input" id="checkAssignAllFlights" type="checkbox"
                                        name="checkAssignAllFlights">
                                    <label class="custom-control-label" for="checkAssignAllFlights"><abbr
                                            title="When enabled, all flights, regardless of being booked will be auto-assigned">Auto-assign
                                            all flights?</abbr></label>
                                </div>
                            </div>
                        </div>

                        {{-- Auto-Assign FL / Route --}}
                        <div class="form-group row mb-0">
                            <div class="col-md-6 offset-md-4">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-check"></i> Auto-Assign FL / Route
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

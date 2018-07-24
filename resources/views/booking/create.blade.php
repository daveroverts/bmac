@extends('layouts.app')

@section('content')
    @if (count($errors) > 0)
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ $event->name }} | Add Timeslots</div>

                <div class="card-body">
                    <form method="POST" action="{{ route('booking.store',$event->id) }}">
                        @csrf
                        <input type="hidden" name="id" value="{{ $event->id }}">
                        {{--Event--}}
                        <div class="form-group row">
                            <label for="event" class="col-md-4 col-form-label text-md-right">Event</label>

                            <div class="col-md-6">
                                <div class="form-control-plaintext">{{ $event->name }}</div>

                            </div>
                        </div>

                        {{--When--}}
                        <div class="form-group row">
                            <label for="when" class="col-md-4 col-form-label text-md-right">When</label>

                            <div class="col-md-6">
                                <div class="form-control-plaintext">{{ $event->startEvent->format('d-m-Y') }}
                                    | {{ $event->startEvent->format('Hi') }}z - {{ $event->endEvent->format('Hi') }}z
                                </div>

                            </div>
                        </div>

                        {{--From--}}
                        <div class="form-group row">
                            <label for="end" class="col-md-4 col-form-label text-md-right"> From</label>

                            <div class="col-md-6">
                                <select class="form-control{{ $errors->has('from') ? ' is-invalid' : '' }}" name="from">
                                    <option value="">Choose an airport...</option>
                                    @foreach($airports as $airport)
                                        <option {{ old('from') == $airport->icao ? 'selected' : '' }}>{{ $airport->icao }}</option>
                                    @endforeach
                                </select>

                                @if ($errors->has('from'))
                                    <span class="invalid-feedback">
                                        <strong>{{ $errors->first('from') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        {{--To--}}
                        <div class="form-group row">
                            <label for="end" class="col-md-4 col-form-label text-md-right"> To</label>

                            <div class="col-md-6">
                                <select class="form-control{{ $errors->has('to') ? ' is-invalid' : '' }}" name="to">
                                    <option value="">Choose an airport...</option>
                                    @foreach($airports as $airport)
                                        <option {{ old('from') == $airport->icao ? 'selected' : '' }}>{{ $airport->icao }}</option>
                                    @endforeach
                                </select>

                                @if ($errors->has('to'))
                                    <span class="invalid-feedback">
                                        <strong>{{ $errors->first('to') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        {{--Start--}}
                        <div class="form-group row">
                            <label for="start" class="col-md-4 col-form-label text-md-right"> Start</label>

                            <div class="col-md-6">
                                <input id="start" type="time"
                                       class="form-control{{ $errors->has('start') ? ' is-invalid' : '' }}" name="start"
                                       value="{{ old('start') }}" required autofocus>

                                @if ($errors->has('start'))
                                    <span class="invalid-feedback">
                                        <strong>{{ $errors->first('start') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        {{--End--}}
                        <div class="form-group row">
                            <label for="end" class="col-md-4 col-form-label text-md-right"> End</label>

                            <div class="col-md-6">
                                <input id="end" type="time"
                                       class="form-control{{ $errors->has('end') ? ' is-invalid' : '' }}" name="end"
                                       value="{{ old('end') }}" required>

                                @if ($errors->has('end'))
                                    <span class="invalid-feedback">
                                        <strong>{{ $errors->first('end') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        {{--Separation--}}
                        <div class="form-group row">
                            <label for="separation" class="col-md-4 col-form-label text-md-right"> Separation (in
                                minutes)</label>

                            <div class="col-md-6">
                                <input id="separation" type="number"
                                       class="form-control{{ $errors->has('separation') ? ' is-invalid' : '' }}"
                                       name="separation" value="{{ old('separation') }}" required min="2">

                                @if ($errors->has('separation'))
                                    <span class="invalid-feedback">
                                        <strong>{{ $errors->first('separation') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        {{--Add--}}
                        <div class="form-group row mb-0">
                            <div class="col-md-6 offset-md-4">
                                <button type="submit" class="btn btn-primary">
                                    Add Timeslots
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

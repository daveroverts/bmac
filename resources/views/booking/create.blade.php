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
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ $event->name }} | Add {{ $bulk ? 'Timeslots' : 'Slot' }}</div>

                <div class="card-body">
                    <form method="POST" action="{{ route('booking.store',$event) }}">
                        @csrf
                        <input type="hidden" name="id" value="{{ $event->id }}">
                        <input type="hidden" name="bulk" value="{{ $bulk ? 1 : 0 }}">
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

                        @if(!$bulk)
                            {{--Callsign--}}
                            <div class="form-group row">
                                <label for="callsign" class="col-md-4 col-form-label text-md-right">Callsign</label>

                                <div class="col-md-6">
                                    <input id="callsign" type="text"
                                           class="form-control{{ $errors->has('callsign') ? ' is-invalid' : '' }}"
                                           name="callsign" value="{{ old('callsign') }}" required max="7">

                                    @if ($errors->has('callsign'))
                                        <span class="invalid-feedback">
                                        <strong>{{ $errors->first('callsign') }}</strong>
                                    </span>
                                    @endif
                                </div>
                            </div>

                            {{--Aircraft--}}
                            <div class="form-group row">
                                <label for="aircraft" class="col-md-4 col-form-label text-md-right"> Aircraft code</label>

                                <div class="col-md-6">
                                    <input id="aircraft" type="text"
                                           class="form-control{{ $errors->has('aircraft') ? ' is-invalid' : '' }}"
                                           name="aircraft" value="{{ old('aircraft') }}" required min="3" max="4">

                                    @if ($errors->has('aircraft'))
                                        <span class="invalid-feedback">
                                        <strong>{{ $errors->first('aircraft') }}</strong>
                                    </span>
                                    @endif
                                </div>
                            </div>
                        @endif

                        {{--From--}}
                        <div class="form-group row">
                            <label for="end" class="col-md-4 col-form-label text-md-right"> From</label>

                            <div class="col-md-6">
                                <select class="custom-select form-control{{ $errors->has('from') ? ' is-invalid' : '' }}" name="from">
                                    <option value="">Choose an airport...</option>
                                    @foreach($airports as $airport)
                                        <option value="{{ $airport->icao }}" {{ old('from') == $airport->icao ? 'selected' : '' }}>{{ $airport->icao }} [{{ $airport->name }} ({{ $airport->iata }})]</option>
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
                                <select class="custom-select form-control{{ $errors->has('to') ? ' is-invalid' : '' }}" name="to">
                                    <option value="">Choose an airport...</option>
                                    @foreach($airports as $airport)
                                        <option value="{{ $airport->icao }}" {{ old('to') == $airport->icao ? 'selected' : '' }}>{{ $airport->icao }} [{{ $airport->name }} ({{ $airport->iata }})]</option>
                                    @endforeach
                                </select>

                                @if ($errors->has('to'))
                                    <span class="invalid-feedback">
                                        <strong>{{ $errors->first('to') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        @if($bulk)
                            {{--Start--}}
                            <div class="form-group row">
                                <label for="start" class="col-md-4 col-form-label text-md-right"> Start</label>

                                <div class="col-md-6">
                                    <input id="start" type="time"
                                           class="form-control{{ $errors->has('start') ? ' is-invalid' : '' }}" name="start"
                                           value="{{ old('start',$event->startEvent->format('H:i')) }}" required autofocus>

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
                                           value="{{ old('end',$event->endEvent->format('H:i')) }}" required>

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
                                           name="separation" value="{{ old('separation',2) }}" required min="1">

                                    @if ($errors->has('separation'))
                                        <span class="invalid-feedback">
                                        <strong>{{ $errors->first('separation') }}</strong>
                                    </span>
                                    @endif
                                </div>
                            </div>
                        @else
                            {{--CTOT--}}
                            <div class="form-group row">
                                <label for="ctot" class="col-md-4 col-form-label text-md-right">CTOT</label>

                                <div class="col-md-6">
                                    <input id="ctot" type="time"
                                           class="form-control{{ $errors->has('ctot') ? ' is-invalid' : '' }}" name="ctot"
                                           value="{{ old('ctot', $event->startEvent->format('H:i')) }}"
                                           required>

                                    @if ($errors->has('ctot'))
                                        <span class="invalid-feedback">
                                        <strong>{{ $errors->first('ctot') }}</strong>
                                    </span>
                                    @endif
                                </div>
                            </div>

                            {{--Route--}}
                            <div class="form-group row">
                                <label for="route" class="col-md-4 col-form-label text-md-right">Route</label>

                                <div class="col-md-6">
                                <textarea class="form-control" id="route"
                                          name="route">{{ old('route','vRoute') }}</textarea>

                                    @if ($errors->has('route'))
                                        <span class="invalid-feedback">
                                        <strong>{{ $errors->first('route') }}</strong>
                                    </span>
                                    @endif
                                </div>
                            </div>

                            {{--Cruise FL--}}
                            <div class="form-group row">
                                <label for="cruiseFL" class="col-md-4 col-form-label text-md-right">Cruise FL</label>

                                <div class="col-md-6">
                                    <input id="oceanicFL" type="text"
                                           class="form-control{{ $errors->has('oceanicFL') ? ' is-invalid' : '' }}"
                                           name="oceanicFL"
                                           value="{{ old('oceanicFL',240) }}" min="3" max="3">

                                    @if ($errors->has('oceanicFL'))
                                        <span class="invalid-feedback">
                                        <strong>{{ $errors->first('oceanicFL') }}</strong>
                                    </span>
                                    @endif
                                </div>
                            </div>
                        @endif

                        {{--Add--}}
                        <div class="form-group row mb-0">
                            <div class="col-md-6 offset-md-4">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-check"></i> Add {{ $bulk ? 'Timeslots' : 'Slot' }}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

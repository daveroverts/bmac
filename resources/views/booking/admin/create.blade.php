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
                    <form method="POST" action="{{ route('admin.bookings.store',$event) }}">
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

                        {{--Editable?--}}
                        <div class="form-group row">
                            <label for="is_editable" class="col-md-4 col-form-label text-md-right"> <abbr
                                    title="Choose if you want the booking to be editable (Callsign and Aircraft Code only) by users. This is useful when using 'import only', but want to add extra slots">Editable?</abbr></label>
                            <div class="col-md-6">
                                <div class="custom-control custom-control-inline custom-radio">
                                    <input type="radio" value="1" id="is_editable1"
                                           name="is_editable"
                                           class="custom-control-input" {{ old('is_editable') == 1 ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="is_editable1">Yes</label>
                                </div>
                                <div class="custom-control custom-control-inline custom-radio">
                                    <input type="radio" value="0" id="is_editable0"
                                           name="is_editable"
                                           class="custom-control-input" {{ old('is_editable') == 0 ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="is_editable0">No</label>
                                </div>

                                @if ($errors->has('is_editable'))
                                    <span class="invalid-feedback">
                                        <strong>{{ $errors->first('is_editable') }}</strong>
                                    </span>
                                @endif
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
                                <label for="aircraft" class="col-md-4 col-form-label text-md-right"> Aircraft
                                    code</label>

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

                        {{--ADEP--}}
                        <div class="form-group row">
                            <label for="end" class="col-md-4 col-form-label text-md-right"> ADEP</label>

                            <div class="col-md-6">
                                <select class="custom-select form-control{{ $errors->has('dep') ? ' is-invalid' : '' }}"
                                        name="dep">
                                    <option value="">Choose an airport...</option>
                                    @foreach($airports as $airport)
                                        <option value="{{ $airport->id }}" {{ old('dep', $event->dep) == $airport->id ? 'selected' : '' }}>{{ $airport->icao }}
                                            [{{ $airport->name }} ({{ $airport->iata }})]
                                        </option>
                                    @endforeach
                                </select>

                                @if ($errors->has('dep'))
                                    <span class="invalid-feedback">
                                        <strong>{{ $errors->first('dep') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        {{--ADES--}}
                        <div class="form-group row">
                            <label for="end" class="col-md-4 col-form-label text-md-right"> ADES</label>

                            <div class="col-md-6">
                                <select class="cusarrm-select form-control{{ $errors->has('arr') ? ' is-invalid' : '' }}"
                                        name="arr">
                                    <option value="">Choose an airport...</option>
                                    @foreach($airports as $airport)
                                        <option value="{{ $airport->id }}" {{ old('arr', $event->arr) == $airport->id ? 'selected' : '' }}>{{ $airport->icao }}
                                            [{{ $airport->name }} ({{ $airport->iata }})]
                                        </option>
                                    @endforeach
                                </select>

                                @if ($errors->has('arr'))
                                    <span class="invalid-feedback">
                                        <strong>{{ $errors->first('arr') }}</strong>
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
                                           class="form-control{{ $errors->has('start') ? ' is-invalid' : '' }}"
                                           name="start"
                                           value="{{ old('start',$event->startEvent->format('H:i')) }}" required
                                           autofocus>

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
                                           name="separation" value="{{ old('separation',2) }}" required step=".01">

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
                                           class="form-control{{ $errors->has('ctot') ? ' is-invalid' : '' }}"
                                           name="ctot"
                                           value="{{ old('ctot') }}">

                                    @if ($errors->has('ctot'))
                                        <span class="invalid-feedback">
                                        <strong>{{ $errors->first('ctot') }}</strong>
                                    </span>
                                    @endif
                                </div>
                            </div>

                            {{--ETA--}}
                            <div class="form-group row">
                                <label for="eta" class="col-md-4 col-form-label text-md-right">ETA</label>

                                <div class="col-md-6">
                                    <input id="eta" type="time"
                                           class="form-control{{ $errors->has('eta') ? ' is-invalid' : '' }}" name="eta"
                                           value="{{ old('eta') }}">

                                    @if ($errors->has('eta'))
                                        <span class="invalid-feedback">
                                        <strong>{{ $errors->first('eta') }}</strong>
                                    </span>
                                    @endif
                                </div>
                            </div>

                            {{--Route--}}
                            <div class="form-group row">
                                <label for="route" class="col-md-4 col-form-label text-md-right">Route</label>

                                <div class="col-md-6">
                                <textarea class="form-control" id="route"
                                          name="route">{{ old('route') }}</textarea>

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
                                           value="{{ old('oceanicFL') }}" min="3" max="3">

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

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
    @include('layouts.alert')
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ $booking->event->name }} | Edit Booking</div>

                <div class="card-body">
                    <form method="POST" action="{{ route('bookings.admin.update',$booking) }}">
                        @csrf
                        @method('PATCH')

                        {{--Callsign--}}
                        <div class="form-group row">
                            <label for="callsign" class="col-md-4 col-form-label text-md-right">Callsign</label>

                            <div class="col-md-6">
                                <input id="callsign" type="text"
                                       class="form-control{{ $errors->has('callsign') ? ' is-invalid' : '' }}"
                                       name="callsign" value="{{ old('callsign',$booking->callsign) }}" autofocus
                                       max="7">

                                @if ($errors->has('callsign'))
                                    <span class="invalid-feedback">
                                        <strong>{{ $errors->first('callsign') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        {{--CTOT--}}
                        <div class="form-group row">
                            <label for="ctot" class="col-md-4 col-form-label text-md-right">CTOT</label>

                            <div class="col-md-6">
                                <input id="ctot" type="time"
                                       class="form-control{{ $errors->has('ctot') ? ' is-invalid' : '' }}" name="ctot"
                                       value="{{ old('ctot', \Carbon\Carbon::parse($booking->getOriginal('ctot'))->format('H:i')) }}">

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
                                       value="{{ old('eta', \Carbon\Carbon::parse($booking->getOriginal('eta'))->format('H:i')) }}">

                                @if ($errors->has('eta'))
                                    <span class="invalid-feedback">
                                        <strong>{{ $errors->first('eta') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        {{--ADEP--}}
                        <div class="form-group row">
                            <label for="end" class="col-md-4 col-form-label text-md-right"> ADEP</label>

                            <div class="col-md-6">
                                <select class="form-control{{ $errors->has('ADEP') ? ' is-invalid' : '' }}" name="ADEP">
                                    <option value="">Choose an airport...</option>
                                    @foreach($airports as $airport)
                                        <option value="{{ $airport->icao }}" {{ old('ADEP', $booking->dep) == $airport->icao ? 'selected' : '' }}>{{ $airport->icao }}
                                            [{{ $airport->name }} ({{ $airport->iata }})]
                                        </option>
                                    @endforeach
                                </select>

                                @if ($errors->has('ADEP'))
                                    <span class="invalid-feedback">
                                        <strong>{{ $errors->first('ADEP') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        {{--ADES--}}
                        <div class="form-group row">
                            <label for="end" class="col-md-4 col-form-label text-md-right"> ADES</label>

                            <div class="col-md-6">
                                <select class="form-control{{ $errors->has('ADES') ? ' is-invalid' : '' }}" name="ADES">
                                    <option value="">Choose an airport...</option>
                                    @foreach($airports as $airport)
                                        <option value="{{ $airport->icao }}" {{ old('ADES', $booking->arr) == $airport->icao ? 'selected' : '' }}>{{ $airport->icao }}
                                            [{{ $airport->name }} ({{ $airport->iata }})]
                                        </option>
                                    @endforeach
                                </select>

                                @if ($errors->has('ADES'))
                                    <span class="invalid-feedback">
                                        <strong>{{ $errors->first('ADES') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        {{--PIC--}}
                        <div class="form-group row">
                            <label for="pic" class="col-md-4 col-form-label text-md-right">PIC</label>

                            <div class="col-md-6">
                                <div class="form-control-plaintext">
                                    <strong>{{ $booking->user ? $booking->user->pic : '-' }}</strong></div>
                            </div>
                        </div>

                        {{--Route--}}
                        <div class="form-group row">
                            <label for="route" class="col-md-4 col-form-label text-md-right">Route</label>

                            <div class="col-md-6">
                                <textarea class="form-control" id="route"
                                          name="route">{{ old('route',$booking->route) }}</textarea>

                                @if ($errors->has('route'))
                                    <span class="invalid-feedback">
                                        <strong>{{ $errors->first('route') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        {{--Track--}}
                        <div class="form-group row">
                            <label for="oceanicTrack" class="col-md-4 col-form-label text-md-right">Track</label>

                            <div class="col-md-6">
                                <input id="oceanicTrack" type="text"
                                       class="form-control{{ $errors->has('oceanicTrack') ? ' is-invalid' : '' }}"
                                       name="oceanicTrack"
                                       value="{{ old('oceanicTrack',$booking->getOriginal('oceanicTrack')) }}" max="2">

                                @if ($errors->has('oceanicTrack'))
                                    <span class="invalid-feedback">
                                        <strong>{{ $errors->first('oceanicTrack') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        {{--Oceanic Entry FL--}}
                        <div class="form-group row">
                            <label for="oceanicFL" class="col-md-4 col-form-label text-md-right">Oceanic Entry
                                FL</label>

                            <div class="col-md-6">
                                <input id="oceanicFL" type="text"
                                       class="form-control{{ $errors->has('oceanicFL') ? ' is-invalid' : '' }}"
                                       name="oceanicFL"
                                       value="{{ old('oceanicFL',$booking->getOriginal('oceanicFL')) }}" max="3">

                                @if ($errors->has('oceanicFL'))
                                    <span class="invalid-feedback">
                                        <strong>{{ $errors->first('oceanicFL') }}</strong>
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
                                       name="aircraft" value="{{ old('aircraft', $booking->acType) }}" min="3" max="4">

                                @if ($errors->has('aircraft'))
                                    <span class="invalid-feedback">
                                        <strong>{{ $errors->first('aircraft') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        {{--SELCAL--}}
                        {{--<div class="form-group row">--}}
                        {{--<label for="selcal" class="col-md-4 col-form-label text-md-right">SELCAL</label>--}}

                        {{--<div class="col-md-6">--}}
                        {{--<div class="form-control-plaintext"><strong>{{ $booking->selcal }}</strong></div>--}}

                        {{--</div>--}}
                        {{--</div>--}}

                        {{--Message--}}
                        <div class="form-group row">
                            <label for="message" class="col-md-4 col-form-label text-md-right">Message</label>

                            <div class="col-md-6">
                                <textarea class="form-control" id="message"
                                          name="message"></textarea>

                                @if ($errors->has('route'))
                                    <span class="invalid-feedback">
                                        <strong>{{ $errors->first('message') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        {{--Update--}}
                        <div class="form-group row mb-0">
                            <div class="col-md-6 offset-md-4">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-check"></i> Update
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

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
                    <form method="POST" action="{{ route('admin.bookings.update',$booking) }}">
                        @csrf
                        @method('PATCH')

                        {{--Editable?--}}
                        <div class="form-group row">
                            <label for="is_editable" class="col-md-4 col-form-label text-md-right"> <abbr
                                    title="Choose if you want the booking to be editable (Callsign and Aircraft Code only) by users. This is useful when using 'import only', but want to add extra slots">Editable?</abbr></label>
                            <div class="col-md-6">
                                <div class="custom-control custom-control-inline custom-radio">
                                    <input type="radio" value="1" id="is_editable1"
                                           name="is_editable"
                                           class="custom-control-input" {{ old('is_editable', $booking->is_editable) == 1 ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="is_editable1">Yes</label>
                                </div>
                                <div class="custom-control custom-control-inline custom-radio">
                                    <input type="radio" value="0" id="is_editable0"
                                           name="is_editable"
                                           class="custom-control-input" {{ old('is_editable', $booking->is_editable) == 0 ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="is_editable0">No</label>
                                </div>

                                @if ($errors->has('is_editable'))
                                    <span class="invalid-feedback">
                                        <strong>{{ $errors->first('is_editable') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

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
                                       value="{{ old('ctot', !empty($flight->getOriginal('ctot')) ? \Carbon\Carbon::parse($flight->getOriginal('ctot'))->format('H:i') : '') }}">

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
                                       value="{{ old('eta', !empty($flight->getOriginal('eta')) ? \Carbon\Carbon::parse($flight->getOriginal('eta'))->format('H:i') : '') }}">

                                @if ($errors->has('eta'))
                                    <span class="invalid-feedback">
                                        <strong>{{ $errors->first('eta') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        {{--ADEP--}}
                        <div class="form-group row">
                            <label for="dep" class="col-md-4 col-form-label text-md-right"> ADEP</label>

                            <div class="col-md-6">
                                <select class="form-control{{ $errors->has('dep') ? ' is-invalid' : '' }}" name="dep">
                                    <option value="">Choose an airport...</option>
                                    @foreach($airports as $airport)
                                        <option
                                            value="{{ $airport->id }}" {{ old('dep', $flight->dep) == $airport->id ? 'selected' : '' }}>{{ $airport->icao }}
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
                            <label for="arr" class="col-md-4 col-form-label text-md-right"> ADES</label>

                            <div class="col-md-6">
                                <select class="form-control{{ $errors->has('arr') ? ' is-invalid' : '' }}" name="arr">
                                    <option value="">Choose an airport...</option>
                                    @foreach($airports as $airport)
                                        <option
                                            value="{{ $airport->id }}" {{ old('arr', $flight->arr) == $airport->id ? 'selected' : '' }}>{{ $airport->icao }}
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
                                          name="route">{{ old('route',$flight->route) }}</textarea>

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
                                       value="{{ old('oceanicTrack',$flight->getOriginal('oceanicTrack')) }}" max="2">

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
                                       value="{{ old('oceanicFL',$flight->getOriginal('oceanicFL')) }}" max="3">

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
                                @if(!empty($booking->user_id))
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" id="notify-user" name="notify_user" value="1" checked>
                                        <label class="custom-control-label" for="notify-user">Notify user?</label>
                                    </div>
                                @endif
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

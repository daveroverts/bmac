@extends('layouts.app')

@section('content')
    @if (count($errors) > 0)
        <div class = "alert alert-danger">
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
                <div class="card-header">{{ $booking->event->name }} | My reservation</div>

                <div class="card-body">
                    <form method="POST" action="{{ route('booking.update',$booking->id) }}">
                        @csrf
                        {{--Callsign--}}
                        <div class="form-group row">
                            <label for="callsign" class="col-md-4 col-form-label text-md-right"> Callsign</label>

                            <div class="col-md-6">
                                <input id="callsign" type="text" class="form-control{{ $errors->has('callsign') ? ' is-invalid' : '' }}" name="callsign" value="{{ old('callsign') }}" required autofocus max="7">

                                @if ($errors->has('callsign'))
                                    <span class="invalid-feedback">
                                        <strong>{{ $errors->first('callsign') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        {{--CTOT--}}
                        <div class="form-group row">
                            <label for="ctot" class="col-md-4 col-form-label text-md-right"> CTOT</label>

                            <div class="col-md-6">
                                <input type="text" readonly class="form-control-plaintext" name="ctot" value="{{ $booking->ctot }}z">

                            </div>
                        </div>

                        {{--ADEP--}}
                        <div class="form-group row">
                            <label for="adep" class="col-md-4 col-form-label text-md-right">ADEP</label>

                            <div class="col-md-6">
                                <input type="text" readonly class="form-control-plaintext" name="adep" value="{{ $booking->dep }}">

                            </div>
                        </div>

                        {{--ADES--}}
                        <div class="form-group row">
                            <label for="ades" class="col-md-4 col-form-label text-md-right">ADES</label>

                            <div class="col-md-6">
                                <input type="text" readonly class="form-control-plaintext" name="ades" value="{{ $booking->arr }}">

                            </div>
                        </div>

                        {{--PIC--}}
                        <div class="form-group row">
                            <label for="pic" class="col-md-4 col-form-label text-md-right">PIC</label>

                            <div class="col-md-6">
                                <input type="text" readonly class="form-control-plaintext" name="pic" value="{{ $booking->reservedBy->name }} | {{ $booking->reservedBy->vatsim_id }}">

                            </div>
                        </div>

                        {{--Route--}}
                        <div class="form-group row">
                            <label for="route" class="col-md-4 col-form-label text-md-right">Route</label>

                            <div class="col-md-6">
                                <input type="text" readonly class="form-control-plaintext" name="route" value="{{ $booking->route ? $booking->route : 'T.B.D. / Available on day of event at 0600z' }}">

                            </div>
                        </div>

                        {{--Track--}}
                        <div class="form-group row">
                            <label for="track" class="col-md-4 col-form-label text-md-right">Track</label>

                            <div class="col-md-6">
                                <input type="text" readonly class="form-control-plaintext" name="track" value="{{ $booking->track ? $booking->track : 'T.B.D. / Available on day of event at 0600z' }}">

                            </div>
                        </div>

                        {{--Oceanic Entry FL--}}
                        <div class="form-group row">
                            <label for="track" class="col-md-4 col-form-label text-md-right">Oceanic Entry FL</label>

                            <div class="col-md-6">
                                <input type="text" readonly class="form-control-plaintext" name="oceanicFL" value="FL{{ $booking->oceanicFL ? $booking->oceanicFL. ' / Subject to change' : 'T.B.D.'}}">

                            </div>
                        </div>

                        {{--Aircraft--}}
                        <div class="form-group form-row">
                            <label for="aircraft" class="col-md-4 col-form-label text-md-right"> Aircraft [ICAO code]</label>

                            <div class="col-md-6">
                                <input id="aircraft" type="text" class="form-control{{ $errors->has('aircraft') ? ' is-invalid' : '' }}" name="aircraft" value="{{ old('aircraft') }}" required max="4">

                                @if ($errors->has('aircraft'))
                                    <span class="invalid-feedback">
                                        <strong>{{ $errors->first('aircraft') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        {{--SELCAL--}}
                        <div class="form-group form-row align-items-center">
                            <label for="selcal" class="col-md-4 col-form-label text-md-right"> Selcal</label>
                            <div class="col-sm-3 my-1">
                                <label class="sr-only" for="selcal1"></label>
                                <input type="text" class="form-control" id="selcal1" placeholder="AB" min="2" max="2">
                            </div>
                            -
                            <div class="col-sm-3 my-1">
                                <label class="sr-only" for="selcal2"></label>
                                <input type="text" class="form-control" id="selcal2" placeholder="CD" min="2" max="2">
                            </div>
                        </div>

                        {{--Study--}}
                        <div class="form-group row">
                            <div class="col-md-8 offset-md-3">
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" name="checkStudy"> I agree to study the provided briefing material
                                    </label>
                                </div>
                            </div>
                        </div>
                            {{--Charts--}}
                        <div class="form-group row">
                            <div class="col-md-8 offset-md-3">
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" name="checkCharts"> I agree to have the applicable charts at hand during the event
                                    </label>
                                </div>
                            </div>
                        </div>

                </div>
            </div>
        </div>
    </div>
@endsection

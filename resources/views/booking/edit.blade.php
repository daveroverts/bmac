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
                <div class="card-header">{{ $booking->event->name }} | My reservation</div>

                <div class="card-body">
                    <form method="POST" action="{{ route('booking.update',$booking) }}">
                        @csrf
                        @method('PATCH')

                        {{--Callsign--}}
                        <div class="form-group row">
                            <label for="callsign" class="col-md-4 col-form-label text-md-right">Callsign</label>

                            <div class="col-md-6">
                                <input id="callsign" type="text"
                                       class="form-control{{ $errors->has('callsign') ? ' is-invalid' : '' }}"
                                       name="callsign" value="{{ old('callsign', $booking->callsign) }}" required autofocus max="7">

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
                                <div class="form-control-plaintext"><strong>{{ $booking->ctot }}</strong></div>

                            </div>
                        </div>

                        {{--ADEP--}}
                        <div class="form-group row">
                            <label for="adep" class="col-md-4 col-form-label text-md-right">ADEP</label>

                            <div class="col-md-6">
                                <div class="form-control-plaintext"><strong>{{ $booking->dep }}</strong></div>

                            </div>
                        </div>

                        {{--ADES--}}
                        <div class="form-group row">
                            <label for="ades" class="col-md-4 col-form-label text-md-right">ADES</label>

                            <div class="col-md-6">
                                <div class="form-control-plaintext"><strong>{{ $booking->arr }}</strong></div>

                            </div>
                        </div>

                        {{--PIC--}}
                        <div class="form-group row">
                            <label for="pic" class="col-md-4 col-form-label text-md-right">PIC</label>

                            <div class="col-md-6">
                                <div class="form-control-plaintext">
                                    <strong>{{ $booking->bookedBy ? $booking->bookedBy->pic : $booking->reservedBy->pic }}</strong>
                                </div>
                            </div>
                        </div>

                        {{--Route--}}
                        <div class="form-group row">
                            <label for="route" class="col-md-4 col-form-label text-md-right">Route</label>

                            <div class="col-md-6">
                                <div class="form-control-plaintext">
                                    <strong>{{ $booking->route ?: 'T.B.D. / Available on day of event at 0600z' }}</strong>
                                </div>

                            </div>
                        </div>

                        {{--Track--}}
                        <div class="form-group row">
                            <label for="track" class="col-md-4 col-form-label text-md-right">Track</label>

                            <div class="col-md-6">
                                <div class="form-control-plaintext">
                                    <strong>{{ $booking->oceanicTrack ?: 'T.B.D. / Available on day of event at 0600z' }}</strong>
                                </div>

                            </div>
                        </div>

                        {{--Oceanic Entry FL--}}
                        <div class="form-group row">
                            <label for="track" class="col-md-4 col-form-label text-md-right">Oceanic Entry FL</label>

                            <div class="col-md-6">
                                <div class="form-control-plaintext"><strong>{{ $booking->oceanicFL }}</strong></div>

                            </div>
                        </div>

                        {{--Aircraft--}}
                        <div class="form-group form-row">
                            <label for="aircraft" class="col-md-4 col-form-label text-md-right"> Aircraft code</label>

                            <div class="col-md-6">
                                <input id="aircraft" type="text"
                                       class="form-control{{ $errors->has('aircraft') ? ' is-invalid' : '' }}"
                                       name="aircraft" value="{{ old('aircraft',$booking->acType) }}" required min="3" max="4">

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
                                <input type="text" class="form-control" id="selcal1" name="selcal1" placeholder="AB"
                                       min="2" max="2" value="{{ old('selcal1',substr($booking->selcal,0,2)) }}">
                            </div>
                            -
                            <div class="col-sm-3 my-1">
                                <label class="sr-only" for="selcal2"></label>
                                <input type="text" class="form-control" id="selcal2" name="selcal2" placeholder="CD"
                                       min="2" max="2" value="{{ old('selcal2',substr($booking->selcal,3,5)) }}">
                            </div>
                        </div>

                        {{--Study--}}
                        <div class="form-group row">
                            <div class="col-md-8 offset-md-3">
                                <div class="custom-control custom-checkbox">
                                    <input type="hidden" name="checkStudy" value="0">
                                    <input class="custom-control-input" id="checkStudy" type="checkbox" name="checkStudy" value="1">
                                    <label class="custom-control-label" for="checkStudy">I agree to study the provided briefing material</label>
                                </div>
                            </div>
                        </div>

                        {{--Charts--}}
                        <div class="form-group row">
                            <div class="col-md-8 offset-md-3">
                                <div class="custom-control custom-checkbox">
                                    <input type="hidden" name="checkCharts" value="0">
                                    <input class="custom-control-input" id="checkCharts" type="checkbox" name="checkCharts" value="1">
                                    <label class="custom-control-label" for="checkCharts">I agree to have the applicable charts at hand during the event</label>
                                </div>
                            </div>
                        </div>

                        {{--Add--}}
                        <div class="form-group row mb-0">
                            <div class="col-md-6 offset-md-4">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-check"></i> Confirm Booking
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

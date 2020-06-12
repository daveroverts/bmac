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
                <div class="card-header">{{ $booking->event->name }} |
                    My {{ $booking->status === \App\Enums\BookingStatus::BOOKED ? 'Booking' : 'Reservation' }}</div>

                <div class="card-body">
                    <form method="POST" action="{{ route('bookings.update',$booking) }}">
                        @csrf
                        @method('PATCH')

                        {{--Callsign--}}
                        <div class="form-group row">
                            <label for="callsign" class="col-md-4 col-form-label text-md-right"> Callsign</label>

                            <div class="col-md-6">
                                @if(!$booking->is_editable)
                                    <div class="form-control-plaintext"><strong>{{ $booking->callsign }}</strong></div>
                                @else
                                    <input id="callsign" type="text"
                                           class="form-control{{ $errors->has('callsign') ? ' is-invalid' : '' }}"
                                           name="callsign"
                                           value="{{ old('callsign', $booking->getOriginal('callsign')) }}" required
                                           autofocus max="7">
                                @endif
                            </div>
                        </div>

                        @if($booking->event->uses_times)
                            @if($booking->getOriginal('ctot'))
                                {{--CTOT--}}
                                <div class="form-group row">
                                    <label for="ctot" class="col-md-4 col-form-label text-md-right"> CTOT</label>

                                    <div class="col-md-6">
                                        <div class="form-control-plaintext"><strong>{{ $flight->FormattedCtot }}</strong></div>

                                    </div>
                                </div>
                            @endif

                            @if($booking->getOriginal('eta'))
                                {{--ETA--}}
                                <div class="form-group row">
                                    <label for="eta" class="col-md-4 col-form-label text-md-right"> ETA</label>

                                    <div class="col-md-6">
                                        <div class="form-control-plaintext"><strong>{{ $flight->formattedEta }}</strong></div>

                                    </div>
                                </div>
                            @endif
                        @endif

                        {{--ADEP--}}
                        <div class="form-group row">
                            <label for="adep" class="col-md-4 col-form-label text-md-right">ADEP</label>

                            <div class="col-md-6">
                                <div class="form-control-plaintext"><strong><abbr
                                            title="{{ $flight->airportDep->name }}">{{ $flight->airportDep->icao }}</abbr></strong>
                                </div>

                            </div>
                        </div>

                        {{--ADES--}}
                        <div class="form-group row">
                            <label for="ades" class="col-md-4 col-form-label text-md-right">ADES</label>

                            <div class="col-md-6">
                                <div class="form-control-plaintext"><strong><abbr
                                            title="{{ $flight->airportArr->name }}">{{ $flight->airportArr->icao }}</abbr></strong>
                                </div>

                            </div>
                        </div>

                        {{--PIC--}}
                        <div class="form-group row">
                            <label for="pic" class="col-md-4 col-form-label text-md-right">PIC</label>

                            <div class="col-md-6">
                                <div class="form-control-plaintext">
                                    <strong>{{ $booking->user->pic }}</strong>
                                </div>
                            </div>
                        </div>

                        {{--Route--}}
                        <div class="form-group row">
                            <label for="route" class="col-md-4 col-form-label text-md-right">Route</label>

                            <div class="col-md-6">
                                <div class="form-control-plaintext">
                                    <strong>{{ $flight->route ?: '-' }}</strong>
                                </div>

                            </div>
                        </div>

                        @if($booking->event->is_oceanic_event)
                            {{--Track--}}
                            <div class="form-group row">
                                <label for="track" class="col-md-4 col-form-label text-md-right">Track</label>

                                <div class="col-md-6">
                                    <div class="form-control-plaintext">
                                        <strong>{{ $flight->oceanicTrack ?: 'T.B.D. / Available on day of event at 0600z' }}</strong>
                                    </div>

                                </div>
                            </div>

                            {{--Oceanic Entry FL--}}
                            <div class="form-group row">
                                <label for="track" class="col-md-4 col-form-label text-md-right">Oceanic Entry
                                    FL</label>

                                <div class="col-md-6">
                                    <div class="form-control-plaintext"><strong>{{ $flight->oceanicFL }}</strong></div>

                                </div>
                            </div>
                        @endif

                        {{--Aircraft--}}
                        <div class="form-group row">
                            <label for="aircraft" class="col-md-4 col-form-label text-md-right"> Aircraft code</label>

                            <div class="col-md-6">
                                @if(!$booking->is_editable)
                                    <div class="form-control-plaintext"><strong>{{ $booking->acType }}</strong></div>
                                @else
                                    <input id="aircraft" type="text"
                                           class="form-control{{ $errors->has('aircraft') ? ' is-invalid' : '' }}"
                                           name="aircraft"
                                           value="{{ old('acType', $booking->getOriginal('acType')) }}" required
                                           max="4">
                                @endif
                            </div>
                        </div>

                        @if($booking->event->is_oceanic_event)
                            {{--SELCAL--}}
                            <div class="form-group form-row align-items-center">
                                <label for="selcal" class="col-md-4 col-form-label text-md-right"> Selcal</label>
                                <div class="col-sm-3 my-1">
                                    <label class="sr-only" for="selcal1"></label>
                                    <input type="text" class="form-control" id="selcal1" name="selcal1" placeholder="AB"
                                           min="2" max="2"
                                           value="{{ old('selcal1',substr($booking->getOriginal('selcal'),0,2)) }}">
                                </div>
                                -
                                <div class="col-sm-3 my-1">
                                    <label class="sr-only" for="selcal2"></label>
                                    <input type="text" class="form-control" id="selcal2" name="selcal2" placeholder="CD"
                                           min="2" max="2"
                                           value="{{ old('selcal2',substr($booking->getOriginal('selcal'),3,5)) }}">
                                </div>
                            </div>
                        @endif

                        @if($booking->status === \App\Enums\BookingStatus::RESERVED)
                            {{--Study--}}
                            <div class="form-group row">
                                <div class="col-md-8 offset-md-3">
                                    <div class="custom-control custom-checkbox">
                                        <input type="hidden" name="checkStudy" value="0">
                                        <input class="custom-control-input" id="checkStudy" type="checkbox"
                                               name="checkStudy" value="1">
                                        <label class="custom-control-label" for="checkStudy">I agree to study the
                                            provided briefing material</label>
                                    </div>
                                </div>
                            </div>

                            {{--Charts--}}
                            <div class="form-group row">
                                <div class="col-md-8 offset-md-3">
                                    <div class="custom-control custom-checkbox">
                                        <input type="hidden" name="checkCharts" value="0">
                                        <input class="custom-control-input" id="checkCharts" type="checkbox"
                                               name="checkCharts" value="1">
                                        <label class="custom-control-label" for="checkCharts">I agree to have the
                                            applicable charts at hand during the event</label>
                                    </div>
                                </div>
                            </div>
                        @endif

                        {{--Add--}}
                        <div class="form-group row mb-0">
                            <div class="col-md-6 offset-md-4">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-check"></i> {{ $booking->bookedBy ? 'Edit' : 'Confirm' }} Booking
                                </button>&nbsp;
                                @if($booking->status === \App\Enums\BookingStatus::RESERVED)
                                    <a href="{{ route('bookings.cancel',$booking) }}" class="btn btn-danger"
                                       onclick="event.preventDefault(); document.getElementById('cancel-form').submit();"><i
                                            class="fa fa-times"></i> Cancel Reservation</a>
                                @endif
                            </div>
                        </div>
                    </form>

                    <form id="cancel-form" action="{{ route('bookings.cancel', $booking) }}" method="post"
                          style="display: none;">
                        @csrf
                        @method('PATCH')
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

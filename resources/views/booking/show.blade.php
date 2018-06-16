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
                    <form method="POST" action="{{ route('booking.cancelBooking',$booking->id) }}">
                        @csrf
                        @method('PUT')
                        {{--Callsign--}}
                        <div class="form-group row">
                            <label for="callsign" class="col-md-4 col-form-label text-md-right">Callsign</label>

                            <div class="col-md-6">
                                <div class="form-control-plaintext">{{ $booking->callsign }}</div>
                            </div>
                        </div>

                        {{--CTOT--}}
                        <div class="form-group row">
                            <label for="ctot" class="col-md-4 col-form-label text-md-right"> CTOT</label>

                            <div class="col-md-6">
                                <div class="form-control-plaintext">{{ $booking->ctot }}</div>

                            </div>
                        </div>

                        {{--ADEP--}}
                        <div class="form-group row">
                            <label for="adep" class="col-md-4 col-form-label text-md-right">ADEP</label>

                            <div class="col-md-6">
                                <div class="form-control-plaintext">{{ $booking->dep }}</div>

                            </div>
                        </div>

                        {{--ADES--}}
                        <div class="form-group row">
                            <label for="ades" class="col-md-4 col-form-label text-md-right">ADES</label>

                            <div class="col-md-6">
                                <div class="form-control-plaintext">{{ $booking->arr }}</div>

                            </div>
                        </div>

                        {{--PIC--}}
                        <div class="form-group row">
                            <label for="pic" class="col-md-4 col-form-label text-md-right">PIC</label>

                            <div class="col-md-6">
                                <div class="form-control-plaintext">{{ $booking->bookedBy ? $booking->bookedBy->pic : $booking->reservedBy->pic }}</div>
                            </div>
                        </div>

                        {{--Route--}}
                        <div class="form-group row">
                            <label for="route" class="col-md-4 col-form-label text-md-right">Route</label>

                            <div class="col-md-6">
                                <div class="form-control-plaintext">{{ $booking->route ? $booking->route : 'T.B.D. / Available on day of event at 0600z' }}</div>

                            </div>
                        </div>

                        {{--Track--}}
                        <div class="form-group row">
                            <label for="track" class="col-md-4 col-form-label text-md-right">Track</label>

                            <div class="col-md-6">
                                <div class="form-control-plaintext">{{ $booking->track ? $booking->track : 'T.B.D. / Available on day of event at 0600z' }}</div>

                            </div>
                        </div>

                        {{--Oceanic Entry FL--}}
                        <div class="form-group row">
                            <label for="track" class="col-md-4 col-form-label text-md-right">Oceanic Entry FL</label>

                            <div class="col-md-6">
                                <div class="form-control-plaintext">{{ $booking->oceanicFL}}</div>

                            </div>
                        </div>

                        {{--Aircraft--}}
                        <div class="form-group row">
                            <label for="aircraft" class="col-md-4 col-form-label text-md-right">Aircraft</label>

                            <div class="col-md-6">
                                <div class="form-control-plaintext">{{ $booking->acType }}</div>
                            </div>
                        </div>

                        {{--SELCAL--}}
                        <div class="form-group row">
                            <label for="selcal" class="col-md-4 col-form-label text-md-right">SELCAL</label>

                            <div class="col-md-6">
                                <div class="form-control-plaintext">{{ $booking->selcal }}</div>
                            </div>
                        </div>

                        {{--Briefing EHAM--}}
                        <div class="form-group row">
                            <label for="briefingEHAM" class="col-md-4 col-form-label text-md-right">Briefing EHAM</label>

                            <div class="col-md-6">
                                <div class="form-control-plaintext"><a href="{{ url('https://www.dutchvacc.nl/index.php?option=com_content&view=article&id=149&Itemid=149') }} " target="_blank">Link</a></div>
                            </div>
                        </div>

                        {{--Charts EHAM--}}
                        <div class="form-group row">
                            <label for="chartsEHAM" class="col-md-4 col-form-label text-md-right">Charts EHAM</label>

                            <div class="col-md-6">
                                <div class="form-control-plaintext"><a href="{{ url('http://scripts.dutchvacc.nl/aipcharts.php?airport=eham') }}" target="_blank" title="Note, it may take up to one minute to load. Please be patient">Link</a></div>
                            </div>
                        </div>

                        {{--Oceanic sheet--}}
                        <div class="form-group row">
                            <label for="chartsEHAM" class="col-md-4 col-form-label text-md-right">Oceanic sheet</label>

                            <div class="col-md-6">
                                <div class="form-control-plaintext"><a href="{{ url('https://ctp.vatsim.net/system/view/includes/Transatlantic_Radio_Operations_Checksheet.pdf') }}" target="_blank">Link</a></div>
                            </div>
                        </div>

                        {{--Briefing KBOS--}}
                        <div class="form-group row">
                            <label for="briefingKBOS" class="col-md-4 col-form-label text-md-right">Briefing KBOS</label>

                            <div class="col-md-6">
                                <div class="form-control-plaintext"><a href="{{ url('') }} " target="_blank">Link</a></div>
                            </div>
                        </div>

                        {{--Charts KBOS--}}
                        <div class="form-group row">
                            <label for="chartsKBOS" class="col-md-4 col-form-label text-md-right">Charts KBOS</label>

                            <div class="col-md-6">
                                <div class="form-control-plaintext"><a href="{{ url('http://www.airnav.com/airport/KBOS') }}" target="_blank">Link</a></div>
                            </div>
                        </div>

                        {{--Cancel Booking--}}
                        <div class="form-group row mb-0">
                            <div class="col-md-6 offset-md-4">
                                <button type="submit" class="btn btn-danger">
                                    Cancel Booking
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

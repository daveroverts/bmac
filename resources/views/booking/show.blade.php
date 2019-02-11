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
    @push('scripts')
        <script>
            $('.cancel-booking').on('click', function (e) {
                e.preventDefault();
                swal({
                    title: 'Are you sure',
                    text: 'Are you sure you want to cancel your booking?',
                    type: 'warning',
                    showCancelButton: true,
                }).then((result) => {
                    if (result.value) {
                        swal('Canceling booking...');
                        swal.showLoading();
                        $(this).closest('form').submit();
                    }
                });
            });
        </script>
    @endpush
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ $booking->event->name }} |
                    My {{ $booking->status === \App\Enums\BookingStatus::BOOKED ? 'Booking' : 'Reservation' }}</div>


                <div class="card-body">
                    {{--Callsign--}}
                    <div class="form-group row">
                        <label for="callsign" class="col-md-4 col-form-label text-md-right">Callsign</label>

                        <div class="col-md-6">
                            <div class="form-control-plaintext"><strong>{{ $booking->callsign }}</strong></div>
                        </div>
                    </div>

                    @if($booking->event->uses_times)
                        {{--CTOT--}}
                        <div class="form-group row">
                            <label for="ctot" class="col-md-4 col-form-label text-md-right"> CTOT</label>

                            <div class="col-md-6">
                                <div class="form-control-plaintext"><strong>{{ $booking->ctot }}</strong></div>

                            </div>
                        </div>

                        {{--ETA--}}
                        <div class="form-group row">
                            <label for="ctot" class="col-md-4 col-form-label text-md-right"> ETA</label>

                            <div class="col-md-6">
                                <div class="form-control-plaintext"><strong>{{ $booking->eta }}</strong></div>

                            </div>
                        </div>
                    @endif

                    {{--ADEP--}}
                    <div class="form-group row">
                        <label for="adep" class="col-md-4 col-form-label text-md-right">ADEP</label>

                        <div class="col-md-6">
                            <div class="form-control-plaintext">
                                <strong>
                                    {!! $booking->airportDep->fullName !!}
                                </strong>
                            </div>

                        </div>
                    </div>

                    {{--ADES--}}
                    <div class="form-group row">
                        <label for="ades" class="col-md-4 col-form-label text-md-right">ADES</label>

                        <div class="col-md-6">
                            <div class="form-control-plaintext">
                                <strong>
                                    {!! $booking->airportArr->fullName !!}
                                </strong>
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

                    @if($booking->event->is_oceanic_event)
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
                                    <strong>{{ $booking->oceanicTrack ?:  'T.B.D. / Available on day of event at 0600z' }}</strong>
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

                    @else
                        {{--Route--}}
                        <div class="form-group row">
                            <label for="route" class="col-md-4 col-form-label text-md-right">Route</label>

                            <div class="col-md-6">
                                <div class="form-control-plaintext">
                                    <strong>{{ $booking->route ?: '-' }}</strong>
                                </div>

                            </div>
                        </div>
                    @endif

                    {{--Aircraft--}}
                    <div class="form-group row">
                        <label for="aircraft" class="col-md-4 col-form-label text-md-right">Aircraft</label>

                        <div class="col-md-6">
                            <div class="form-control-plaintext"><strong>{{ $booking->acType }}</strong></div>
                        </div>
                    </div>

                    @if($booking->event->is_oceanic_event)
                        {{--SELCAL--}}
                        <div class="form-group row">
                            <label for="selcal" class="col-md-4 col-form-label text-md-right">SELCAL</label>

                            <div class="col-md-6">
                                <div class="form-control-plaintext"><strong>{{ $booking->selcal }}</strong></div>
                            </div>
                        </div>
                    @endif

                    @foreach($booking->airportDep->links as $link)
                        <div class="form-group row">
                            <label for="{{ $link->type->name . $link->airport->icao . '-' . $loop->index }}"
                                   class="col-md-4 col-form-label text-md-right">{{ $link->name ?? $link->type->name . ' ' . $link->airport->icao }}</label>

                            <div class="col-md-6">
                                <div class="form-control-plaintext"><a
                                        href="{{ $link->url }}"
                                        target="_blank">Link</a></div>
                            </div>
                        </div>
                    @endforeach

                    @if($booking->event->is_oceanic_event)
                        {{--Oceanic sheet--}}
                        <div class="form-group row">
                            <label for="oceanicSheet" class="col-md-4 col-form-label text-md-right">Oceanic
                                procedures</label>

                            <div class="col-md-6">
                                <div class="form-control-plaintext"><a
                                        href="https://www.virtualnorwegian.net/oceanic/"
                                        target="_blank">Link</a></div>
                            </div>
                        </div>
                    @endif

                    @foreach($booking->airportArr->links as $link)
                        <div class="form-group row">
                            <label for="{{ $link->type->name . $link->airport->icao . '-' . $loop->index }}"
                                   class="col-md-4 col-form-label text-md-right">{{ $link->name ?? $link->type->name . ' ' . $link->airport->icao }}</label>

                            <div class="col-md-6">
                                <div class="form-control-plaintext"><a
                                        href="{{ $link->url }}"
                                        target="_blank">Link</a></div>
                            </div>
                        </div>
                    @endforeach
                    {{--Edit Booking--}}
                    <div class="form-group row mb-0">
                        <div class="col-md-7 offset-md-3">
                            @if(!$booking->event->import_only)
                                <a href="{{ route('bookings.edit',$booking) }}" class="btn btn-primary">Edit Booking</a>
                                &nbsp;
                            @endif
                            {{--Cancel Booking--}}
                            <form method="post" action="{{ route('bookings.cancel', $booking) }}">
                                @csrf
                                @method('PATCH')
                                <button class="btn btn-danger cancel-booking">Cancel Booking</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

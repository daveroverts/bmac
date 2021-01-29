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
                Swal.fire({
                    title: 'Are you sure',
                    text: 'Are you sure you want to cancel your booking?',
                    icon: 'warning',
                    showCancelButton: true,
                }).then((result) => {
                    if (result.value) {
                        Swal.fire('Canceling booking...');
                        Swal.showLoading();
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
                    @foreach($booking->flights as $flight)
                        <div class="form-group row">
                            <label for="ctot" class="col-md-4 col-form-label text-md-right"> <strong>Leg
                                    #{{ $loop->iteration }}</strong></label>
                        </div>

                        @if($booking->event->uses_times)
                            @if($flight->getRawOriginal('ctot'))
                                {{--CTOT--}}
                                <div class="form-group row">
                                    <label for="ctot" class="col-md-4 col-form-label text-md-right"> CTOT</label>

                                    <div class="col-md-6">
                                        <div class="form-control-plaintext"><strong>{{ $flight->formattedCtot }}</strong></div>

                                    </div>
                                </div>
                            @endif

                            @if($flight->getRawOriginal('eta'))
                                {{--ETA--}}
                                <div class="form-group row">
                                    <label for="ctot" class="col-md-4 col-form-label text-md-right"> ETA</label>

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
                                <div class="form-control-plaintext">
                                    <strong>
                                        {!! $flight->airportDep->fullName !!}
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
                                        {!! $flight->airportArr->fullName !!}
                                    </strong>
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

                        @if($flight->getRawOriginal('notes'))
                            {{--Notes--}}
                            <div class="form-group row">
                                <label for="notes" class="col-md-4 col-form-label text-md-right">Notes</label>

                                <div class="col-md-6">
                                    <div class="form-control-plaintext">
                                        <strong>{{ $flight->notes }}</strong>
                                    </div>

                                </div>
                            </div>
                        @endif
                        <hr>
                    @endforeach


                    {{--Callsign--}}
                    <div class="form-group row">
                        <label for="callsign" class="col-md-4 col-form-label text-md-right">Callsign</label>

                        <div class="col-md-6">
                            <div class="form-control-plaintext"><strong>{{ $booking->callsign }}</strong></div>
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

                    {{--Aircraft--}}
                    <div class="form-group row">
                        <label for="aircraft" class="col-md-4 col-form-label text-md-right">Aircraft</label>

                        <div class="col-md-6">
                            <div class="form-control-plaintext"><strong>{{ $booking->acType }}</strong></div>
                        </div>
                    </div>

                    @foreach($booking->event->links as $link)
                        <div class="form-group row">
                            <label for="{{ $link->type->name . '-' . $loop->index }}"
                                   class="col-md-4 col-form-label text-md-right">{{ $link->name ?? $link->type->name}}</label>

                            <div class="col-md-6">
                                <div class="form-control-plaintext"><a
                                        href="{{ $link->url }}"
                                        target="_blank">Link</a></div>
                            </div>
                        </div>
                    @endforeach

                    @foreach($booking->uniqueAirports() as $airport)
                        @foreach($airport->links as $link)
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
                    @endforeach

                    {{--Edit Booking--}}
                    <div class="form-group row mb-0">
                        <div class="col-md-7 offset-md-3">
                            @if($booking->is_editable)
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

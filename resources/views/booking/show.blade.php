@extends('layouts.app')

@section('content')
    <x-forms.alert />
    @push('scripts')
        <script>
            $('.cancel-booking').on('click', function(e) {
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
                        $('#cancel-booking').submit();
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
                    {{-- Callsign --}}
                    <div class="form-group row">
                        <label for="callsign" class="col-md-4 col-form-label text-md-right">Callsign</label>

                        <div class="col-md-6">
                            <div class="form-control-plaintext"><strong>{{ $booking->formatted_callsign }}</strong></div>
                        </div>
                    </div>

                    @if ($booking->event->uses_times)
                        {{-- CTOT --}}
                        <div class="form-group row">
                            <label for="ctot" class="col-md-4 col-form-label text-md-right"> CTOT</label>

                            <div class="col-md-6">
                                <div class="form-control-plaintext"><strong>{{ $flight->formattedCtot }}</strong></div>

                            </div>
                        </div>

                        {{-- ETA --}}
                        <div class="form-group row">
                            <label for="ctot" class="col-md-4 col-form-label text-md-right"> ETA</label>

                            <div class="col-md-6">
                                <div class="form-control-plaintext"><strong>{{ $flight->formattedEta }}</strong></div>

                            </div>
                        </div>
                    @endif

                    {{-- ADEP --}}
                    @if ($flight->dep)
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
                    @endif

                    {{-- ADES --}}
                    @if ($flight->arr)
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
                    @endif

                    {{-- PIC --}}
                    <div class="form-group row">
                        <label for="pic" class="col-md-4 col-form-label text-md-right">PIC</label>

                        <div class="col-md-6">
                            <div class="form-control-plaintext">
                                <strong>{{ $booking->user->pic }}</strong>
                            </div>
                        </div>
                    </div>

                    @if ($booking->event->is_oceanic_event)
                        {{-- Route --}}
                        <div class="form-group row">
                            <label for="route" class="col-md-4 col-form-label text-md-right">Route</label>

                            <div class="col-md-6">
                                <div class="form-control-plaintext">
                                    <strong>{{ $flight->route ?: 'T.B.D. / Available on day of event at 0600z' }}</strong>
                                </div>

                            </div>
                        </div>

                        {{-- Track --}}
                        <div class="form-group row">
                            <label for="track" class="col-md-4 col-form-label text-md-right">Track</label>

                            <div class="col-md-6">
                                <div class="form-control-plaintext">
                                    <strong>{{ $flight->oceanicTrack ?: 'T.B.D. / Available on day of event at 0600z' }}</strong>
                                </div>

                            </div>
                        </div>

                        {{-- Oceanic Entry FL --}}
                        <div class="form-group row">
                            <label for="track" class="col-md-4 col-form-label text-md-right">Oceanic Entry FL</label>

                            <div class="col-md-6">
                                <div class="form-control-plaintext"><strong>{{ $flight->formatted_oceanicfl }}</strong>
                                </div>

                            </div>
                        </div>

                    @else
                        {{-- Route --}}
                        <div class="form-group row">
                            <label for="route" class="col-md-4 col-form-label text-md-right">Route</label>

                            <div class="col-md-6">
                                <div class="form-control-plaintext">
                                    <strong>{{ $flight->route ?: '-' }}</strong>
                                </div>

                            </div>
                        </div>
                    @endif

                    @if ($flight->getRawOriginal('notes'))
                        {{-- Notes --}}
                        <div class="form-group row">
                            <label for="notes" class="col-md-4 col-form-label text-md-right">Notes</label>

                            <div class="col-md-6">
                                <div class="form-control-plaintext">
                                    <strong>{{ $flight->notes }}</strong>
                                </div>

                            </div>
                        </div>
                    @endif

                    {{-- Aircraft --}}
                    <div class="form-group row">
                        <label for="aircraft" class="col-md-4 col-form-label text-md-right">Aircraft</label>

                        <div class="col-md-6">
                            <div class="form-control-plaintext"><strong>{{ $booking->formatted_actype }}</strong></div>
                        </div>
                    </div>

                    @if ($booking->event->is_oceanic_event)
                        {{-- SELCAL --}}
                        <div class="form-group row">
                            <label for="selcal" class="col-md-4 col-form-label text-md-right">SELCAL</label>

                            <div class="col-md-6">
                                <div class="form-control-plaintext"><strong>{{ $booking->selcal }}</strong></div>
                            </div>
                        </div>
                    @endif

                    @foreach ($flight->airportDep->links as $link)
                        <div class="form-group row">
                            <label for="{{ $link->type->name . $link->airport->icao . '-' . $loop->index }}"
                                class="col-md-4 col-form-label text-md-right">{{ $link->name ?? $link->type->name . ' ' . $link->airport->icao }}</label>

                            <div class="col-md-6">
                                <div class="form-control-plaintext"><a href="{{ $link->url }}" target="_blank">Link</a>
                                </div>
                            </div>
                        </div>
                    @endforeach

                    @foreach ($booking->event->links as $link)
                        <div class="form-group row">
                            <label for="{{ $link->type->name . '-' . $loop->index }}"
                                class="col-md-4 col-form-label text-md-right">{{ $link->name ?? $link->type->name }}</label>

                            <div class="col-md-6">
                                <div class="form-control-plaintext"><a href="{{ $link->url }}" target="_blank">Link</a>
                                </div>
                            </div>
                        </div>
                    @endforeach

                    @foreach ($flight->airportArr->links as $link)
                        <div class="form-group row">
                            <label for="{{ $link->type->name . $link->airport->icao . '-' . $loop->index }}"
                                class="col-md-4 col-form-label text-md-right">{{ $link->name ?? $link->type->name . ' ' . $link->airport->icao }}</label>

                            <div class="col-md-6">
                                <div class="form-control-plaintext"><a href="{{ $link->url }}" target="_blank">Link</a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                    <div class="form-group row mb-0">
                        <div class="col-md-7 offset-md-3">
                            @if ($booking->is_editable)
                                {{-- Edit Booking --}}
                                <a href="{{ route('bookings.edit', $booking) }}" class="btn btn-primary mb-2">Edit
                                    Booking</a>
                            @endif
                            {{-- Cancel Booking --}}
                            <button class="btn btn-danger mb-2 cancel-booking" form="cancel-booking">Cancel Booking</button>

                            <form method="post" action="{{ route('bookings.cancel', $booking) }}" id="cancel-booking">
                                @csrf
                                @method('PATCH')

                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

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
                        Swal.fire('Cancelling booking...');
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
                    {{ $booking->status === \App\Enums\BookingStatus::BOOKED ? __('My Booking') : __('My Reservation') }}
                </div>


                <div class="card-body">
                    <x-form-group :label="__('Callsign')">
                        <strong>{{ $booking->formatted_callsign }}</strong>
                    </x-form-group>

                    <x-form-group :label="__('Aircraft code')">
                        <strong>{{ $booking->acType }}</strong>
                    </x-form-group>

                    @if ($booking->event->uses_times)
                        @if ($flight->ctot)
                            <x-form-group :label="__('CTOT')">
                                <strong>{{ $flight->formatted_ctot }}</strong>
                            </x-form-group>
                        @endif

                        @if ($flight->eta)
                            <x-form-group :label="__('ETA')">
                                <strong>{{ $flight->formatted_eta }}</strong>
                            </x-form-group>
                        @endif
                    @endif

                    @if ($flight->dep)
                        <x-form-group :label="__('ADEP')">
                            <strong>{{ $flight->airportDep->icao }} - {{ $flight->airportDep->name }} -
                                {{ $flight->airportDep->iata }}</strong>
                        </x-form-group>
                    @endif

                    @if ($flight->arr)
                        <x-form-group :label="__('ADES')">
                            <strong>{{ $flight->airportArr->icao }} - {{ $flight->airportArr->name }} -
                                {{ $flight->airportArr->iata }}</strong>
                        </x-form-group>
                    @endif

                    <x-form-group :label="__('PIC')">
                        <strong>{{ $booking->user->pic }}</strong>
                    </x-form-group>

                    <x-form-group :label="__('Route')">
                        <strong>{{ $flight->route ?: '-' }}</strong>
                    </x-form-group>

                    @if ($booking->event->is_oceanic_event)
                        <x-form-group :label="__('Track')">
                            <strong>{{ $flight->oceanicTrack ?: 'T.B.D.' }}</strong>
                        </x-form-group>

                        <x-form-group :label="__('Oceanic Entry FL')">
                            <strong>{{ $flight->formatted_oceanicfl }}</strong>
                        </x-form-group>

                        <x-form-group :label="__('SELCAL')" inline>
                            <strong>{{ $flight->booking->formatted_selcal }}</strong>
                        </x-form-group>
                    @else
                        @if ($flight->oceanicFL)
                            <x-form-group :label="__('Cruise FL')">
                                <strong>{{ $flight->formatted_oceanicfl }}</strong>
                            </x-form-group>
                        @endif
                    @endif

                    @if ($flight->notes)
                        <x-form-group :label="__('Notes')">
                            <strong>{{ $flight->formatted_notes }}</strong>
                        </x-form-group>
                    @endif

                    @foreach ($flight->airportDep->links as $link)
                        <x-form-group :label="$link->name ?: $link->type->name . ' ' . $link->airport->icao">
                            <strong>
                                <a href="{{ $link->url }}" rel="noreferrer noopener" target="_blank">Link</a>
                            </strong>
                        </x-form-group>
                    @endforeach

                    @foreach ($booking->event->links as $link)
                        <x-form-group :label="$link->name ?: $link->type->name">
                            <strong>
                                <a href="{{ $link->url }}" rel="noreferrer noopener" target="_blank">Link</a>
                            </strong>
                        </x-form-group>
                    @endforeach

                    @foreach ($flight->airportArr->links as $link)
                        <x-form-group :label="$link->name ?: $link->type->name . ' ' . $link->airport->icao">
                            <strong>
                                <a href="{{ $link->url }}" rel="noreferrer noopener" target="_blank">Link</a>
                            </strong>
                        </x-form-group>
                    @endforeach

                    @if ($startConfirm)
                    @if (now()>$startConfirm && now()<$endConfirm)
                    <x-form-group>
                        Slot Confirmation:
                        @if ($confirmed)
                            Slot was confirmed at {{$confirmed}}
                        @else

                            <button class="btn btn-success confirm-booking" form="confirm-booking">
                            {{ __('Confirm Booking') }}
                        </button>
                        @endif

                    </x-form-group>
                    <x-form :action="route('bookings.confirm', $booking)" id="confirm-booking" method="POST"
                        style="display: none;"></x-form>
                    @endif
                    @endif
                    <x-form-group inline>
                        @if ($booking->is_editable)
                            <a href="{{ route('bookings.edit', $booking) }}" class="btn btn-primary">
                                {{ __('Edit Booking') }}
                            </a>
                        @endif

                        <button class="btn btn-danger cancel-booking" form="cancel-booking">
                            {{ __('Cancel Booking') }}
                        </button>
                    </x-form-group>



                    <x-form :action="route('bookings.cancel', $booking)" id="cancel-booking" method="PATCH"
                        style="display: none;"></x-form>
                </div>
            </div>
        </div>
    </div>
@endsection

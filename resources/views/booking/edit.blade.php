@extends('layouts.app')

@section('content')
    <x-forms.alert />
    @include('layouts.alert')
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ $booking->event->name }} |
                    My {{ $booking->status === \App\Enums\BookingStatus::BOOKED ? 'Booking' : 'Reservation' }}</div>

                <div class="card-body">
                    <x-form :action="route('bookings.update', $booking)" method="PATCH">
                        @bind($booking)
                        @if (!$booking->is_editable)
                            <x-form-group :label="__('Callsign')">
                                <strong>{{ $booking->formatted_callsign }}</strong>
                            </x-form-group>
                            <x-form-group :label="__('Aircraft code')">
                                <strong>{{ $booking->formatted_actype }}</strong>
                            </x-form-group>
                        @else
                            <x-form-input name="callsign" :label="__('Callsign')" required maxlength="7" />
                            <x-form-input name="acType" :label="__('Aircraft code')" required minlength="3" maxlength="4" />
                        @endif

                        @bind($flight)
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
                                <x-form-input name="selcal1" placeholder="AB" minlength="2" maxlength="2" />
                                <x-form-input name="selcal2" placeholder="CD" minlength="2" maxlength="2" />
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

                        @if ($booking->status === \App\Enums\BookingStatus::RESERVED)
                            <x-form-group>
                                <x-form-checkbox name="checkStudy" required
                                    :label="__('I agree to study the provided briefing material')" value="1" />

                                <x-form-checkbox name="checkCharts" required
                                    :label="__('I agree to have the applicable charts at hand during the event')"
                                    value="1" />
                            </x-form-group>

                        @endif

                        <x-form-group inline>
                            <x-form-submit>
                                <i class="fas fa-check"></i> {{ $booking->bookedBy ? 'Edit' : 'Confirm' }} Booking
                            </x-form-submit>

                            @if ($booking->status === \App\Enums\BookingStatus::RESERVED)
                                <button class="btn btn-danger"
                                    onclick="event.preventDefault(); document.getElementById('cancel-form').submit();">
                                    <i class=" fa fa-times"></i> Cancel Reservation
                                </button>
                            @endif
                        </x-form-group>

                        @endbind
                        @endbind
                        </x-form-group>


                        <x-form :action="route('bookings.cancel', $booking)" id="cancel-form" method="PATCH"
                            style="display: none;"></x-form>
                </div>
            </div>
        </div>
    </div>
@endsection

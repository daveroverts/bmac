@extends('layouts.app')

@section('content')
    <x-forms.alert />
    @include('layouts.alert')
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ $booking->event->name }} |
                    {{ $booking->status == \App\Enums\BookingStatus::BOOKED ? __('My Booking') : __('My Reservation') }}
                </div>

                <div class="card-body">
                    <x-form :action="route('bookings.update', $booking)" method="PATCH">
                            @if (!$booking->is_editable)
                                <x-forms.form-group :label="__('Callsign')">
                                    <strong>{{ $booking->formatted_callsign }}</strong>
                                </x-forms.form-group>
                                <x-forms.form-group :label="__('Aircraft code')">
                                    <strong>{{ $booking->formatted_actype }}</strong>
                                </x-forms.form-group>
                            @else
                                <x-forms.input name="callsign" :label="__('Callsign')" required maxlength="7" :value="old('callsign', $booking->callsign)" />
                                <x-forms.input name="acType" :label="__('Aircraft code')" required minlength="3" maxlength="4" :value="old('acType', $booking->acType)" />
                            @endif

                                @if ($booking->event->uses_times)
                                    @if ($flight->ctot)
                                        <x-forms.form-group :label="__('CTOT')">
                                            <strong>{{ $flight->formatted_ctot }}</strong>
                                        </x-forms.form-group>
                                    @endif

                                    @if ($flight->eta)
                                        <x-forms.form-group :label="__('ETA')">
                                            <strong>{{ $flight->formatted_eta }}</strong>
                                        </x-forms.form-group>
                                    @endif
                                @endif

                                @if ($flight->dep)
                                    <x-forms.form-group :label="__('ADEP')">
                                        <strong>{{ $flight->airportDep->icao }} - {{ $flight->airportDep->name }} -
                                            {{ $flight->airportDep->iata }}</strong>
                                    </x-forms.form-group>
                                @endif

                                @if ($flight->arr)
                                    <x-forms.form-group :label="__('ADES')">
                                        <strong>{{ $flight->airportArr->icao }} - {{ $flight->airportArr->name }} -
                                            {{ $flight->airportArr->iata }}</strong>
                                    </x-forms.form-group>
                                @endif

                                <x-forms.form-group :label="__('PIC')">
                                    <strong>{{ $booking->user->pic }}</strong>
                                </x-forms.form-group>

                                <x-forms.form-group :label="__('Route')">
                                    <strong>{{ $flight->route ?: '-' }}</strong>
                                </x-forms.form-group>

                                @if ($booking->event->is_oceanic_event)
                                    <x-forms.form-group :label="__('Track')">
                                        <strong>{{ $flight->oceanicTrack ?: 'T.B.D.' }}</strong>
                                    </x-forms.form-group>

                                    <x-forms.form-group :label="__('Oceanic Entry FL')">
                                        <strong>{{ $flight->formatted_oceanicfl }}</strong>
                                    </x-forms.form-group>

                                    <x-forms.form-group :label="__('SELCAL')" inline>
                                        @php
                                        if (!empty($booking->selcal)) {
                                            $part1 = substr($booking->selcal, 0, 2);
                                            $part2 = substr($booking->selcal, 3, 2);
                                        } else {
                                            $part1 = '';
                                            $part2 = '';
                                        }
                                        @endphp
                                        <x-forms.input name="selcal1" label="" placeholder="AB" minlength="2" maxlength="2" :value="old('selcal1', $part1)" />
                                        <x-forms.input name="selcal2" label="" placeholder="CD" minlength="2" maxlength="2" :value="old('selcal2', $part2)" />
                                    </x-forms.form-group>
                                @else
                                    @if ($flight->oceanicFL)
                                        <x-forms.form-group :label="__('Cruise FL')">
                                            <strong>{{ $flight->formatted_oceanicfl }}</strong>
                                        </x-forms.form-group>
                                    @endif
                                @endif

                                @if ($flight->notes)
                                    <x-forms.form-group :label="__('Notes')">
                                        <strong>{{ $flight->formatted_notes }}</strong>
                                    </x-forms.form-group>
                                @endif

                                @foreach ($flight->airportDep->links as $link)
                                    <x-forms.form-group :label="$link->name ?: $link->type->name . ' ' . $link->airport->icao">
                                        <strong>
                                            <a href="{{ $link->url }}" rel="noreferrer noopener" target="_blank">Link</a>
                                        </strong>
                                    </x-forms.form-group>
                                @endforeach

                                @foreach ($booking->event->links as $link)
                                    <x-forms.form-group :label="$link->name ?: $link->type->name">
                                        <strong>
                                            <a href="{{ $link->url }}" rel="noreferrer noopener" target="_blank">Link</a>
                                        </strong>
                                    </x-forms.form-group>
                                @endforeach

                                @foreach ($flight->airportArr->links as $link)
                                    <x-forms.form-group :label="$link->name ?: $link->type->name . ' ' . $link->airport->icao">
                                        <strong>
                                            <a href="{{ $link->url }}" rel="noreferrer noopener" target="_blank">Link</a>
                                        </strong>
                                    </x-forms.form-group>
                                @endforeach

                                @if ($booking->status === \App\Enums\BookingStatus::RESERVED)
                                    <x-forms.form-group>
                                        <input type="hidden" name="checkStudy" value="0">
                                        <x-forms.checkbox name="checkStudy" required :label="__('I agree to study the provided briefing material')" value="1" />

                                        <input type="hidden" name="checkCharts" value="0">
                                        <x-forms.checkbox name="checkCharts" required :label="__('I agree to have the applicable charts at hand during the event')" value="1" />
                                    </x-forms.form-group>
                                @endif

                                <x-forms.form-group inline>
                                    <x-forms.button type="submit">
                                        <i class="fas fa-check"></i>
                                        {{ $booking->status === \App\Enums\BookingStatus::RESERVED ? 'Confirm' : 'Edit' }} Booking
                                    </x-forms.button>

                                    @if ($booking->status === \App\Enums\BookingStatus::RESERVED)
                                        <button class="btn btn-danger"
                                            onclick="event.preventDefault(); document.getElementById('cancel-form').submit();">
                                            <i class=" fa fa-times"></i> Cancel Reservation
                                        </button>
                                    @endif
                                </x-forms.form-group>
                    </x-form>
                        <x-form :action="route('bookings.cancel', $booking)" id="cancel-form" method="PATCH" style="display: none;"></x-form>
                </div>
            </div>
        </div>
    </div>
@endsection

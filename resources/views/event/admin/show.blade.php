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
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ $event->name }}</div>

                <div class="card-body">

                    {{--Name--}}
                    <div class="form-group row">
                        <label for="name" class="col-md-4 col-form-label text-md-right">Name</label>

                        <div class="col-md-6">
                            <div class="form-control-plaintext"><strong><a href="{{ route('events.show', $event) }}"
                                        title="Open event page as users would see">{{ $event->name }}</a>
                                    | <a href="{{ route('bookings.event.index', $event) }}"
                                        title="Open slot table of the event">Slot table</a></strong></div>
                        </div>
                    </div>

                    {{--Type--}}
                    <div class="form-group row">
                        <label for="type" class="col-md-4 col-form-label text-md-right">Type</label>

                        <div class="col-md-6">
                            <div class="form-control-plaintext"><strong>{{ $event->type->name }}</strong></div>
                        </div>
                    </div>

                    @if ($event->type->id != \App\Enums\EventType::MULTIFLIGHTS)
                        {{--Departure Airport--}}
                        <div class="form-group row">
                            <label for="dep" class="col-md-4 col-form-label text-md-right">Departure Airport</label>

                            <div class="col-md-6">
                                <div class="form-control-plaintext">
                                    <strong>
                                        <a href="{{ route('admin.airports.show', $event->airportDep) }}">
                                            {{ $event->airportDep->name }} [{{ $event->airportDep->icao }}
                                            | {{ $event->airportDep->iata }}]
                                        </a>
                                    </strong>
                                </div>
                            </div>
                        </div>

                        {{--Arrival Airport--}}
                        <div class="form-group row">
                            <label for="arr" class="col-md-4 col-form-label text-md-right">Arrival Airport</label>

                            <div class="col-md-6">
                                <div class="form-control-plaintext">
                                    <strong>
                                        <a href="{{ route('admin.airports.show', $event->airportArr) }}">
                                            {{ $event->airportArr->name }} [{{ $event->airportArr->icao }}
                                            | {{ $event->airportArr->iata }}]
                                        </a>
                                    </strong>
                                </div>
                            </div>
                        </div>
                    @endif

                    {{--Event date/time--}}
                    <div class="form-group row">
                        <label for="eventDateTime" class="col-md-4 col-form-label text-md-right">Event date | time</label>

                        <div class="col-md-6">
                            <div class="form-control-plaintext">
                                <strong>{{ $event->startEvent->toFormattedDateString() }}
                                    | {{ $event->startEvent->format('Hi') }}z - {{ $event->endEvent->format('Hi') }}
                                    z</strong>
                            </div>
                        </div>
                    </div>

                    {{--Booking status--}}
                    <div class="form-group row">
                        <label for="bookingStatus" class="col-md-4 col-form-label text-md-right">Booking status</label>

                        <div class="col-md-6">
                            <div class="form-control-plaintext">
                                <strong>
                                    @if ($event->startBooking >= now())
                                        <u>Bookings</u> will <u>open</u> at
                                        <u>{{ $event->startBooking->toFormattedDateString() }}
                                            {{ $event->startBooking->format('Hi') }}
                                            z</u>
                                    @elseif($event->endBooking <= now()) <u>Bookings locked</u> since
                                            <u>{{ $event->endBooking->toFormattedDateString() }}
                                                {{ $event->endBooking->format('Hi') }}
                                                z</u>
                                        @else
                                            <u>Bookings open</u> since
                                            <u>{{ $event->startBooking->toFormattedDateString() }}
                                                {{ $event->startBooking->format('Hi') }}
                                                z</u><br>
                                            Closes
                                            at {{ $event->endBooking->toFormattedDateString() }}
                                            {{ $event->endBooking->format('Hi') }}
                                            z
                                    @endif
                                </strong>
                            </div>
                        </div>
                    </div>

                    {{--Import only?--}}
                    <div class="form-group row">
                        <label for="importOnly" class="col-md-4 col-form-label text-md-right">
                            <abbr title="If enabled, only admins can fill in details via import script">
                                Only import?
                            </abbr>
                        </label>

                        <div class="col-md-6">
                            <div class="form-control-plaintext">
                                <strong>
                                    {{ $event->import_only ? 'Yes' : 'No' }}
                                </strong>
                            </div>
                        </div>
                    </div>

                    {{--Show times?--}}
                    <div class="form-group row">
                        <label for="usesTimes" class="col-md-4 col-form-label text-md-right">
                            <abbr title="If enabled, CTOT and ETA (if set in booking) will be shown">
                                Show times?
                            </abbr>
                        </label>

                        <div class="col-md-6">
                            <div class="form-control-plaintext">
                                <strong>
                                    {{ $event->uses_times ? 'Yes' : 'No' }}
                                </strong>
                            </div>
                        </div>
                    </div>

                    {{--Multiple bookings allowed?--}}
                    <div class="form-group row">
                        <label for="multipleBookingsAllowed" class="col-md-4 col-form-label text-md-right">
                            <abbr title="If enabled, a user is allowed to book multiple flights for this event">
                                Multiple bookings allowed?
                            </abbr>
                        </label>

                        <div class="col-md-6">
                            <div class="form-control-plaintext">
                                <strong>
                                    {{ $event->multiple_bookings_allowed ? 'Yes' : 'No' }}
                                </strong>
                            </div>
                        </div>
                    </div>

                    {{--Is oceanic event?--}}
                    <div class="form-group row">
                        <label for="isOceanicEvent" class="col-md-4 col-form-label text-md-right">
                            <abbr
                                title="If enabled, users can fill in a SELCAL code, and oceanic links are shown in the booking briefing">
                                Oceanic event?
                            </abbr>
                        </label>

                        <div class="col-md-6">
                            <div class="form-control-plaintext">
                                <strong>
                                    {{ $event->is_oceanic_event ? 'Yes' : 'No' }}
                                </strong>
                            </div>
                        </div>
                    </div>

                    @foreach($event->links as $link)
                        <div class="form-group row">
                            <label for="{{ $link->type->name . '-' . $loop->index }}"
                                   class="col-md-4 col-form-label text-md-right">{{ $link->name ?? $link->type->name }}</label>

                            <div class="col-md-6">
                                <div class="form-control-plaintext"><a
                                        href="{{ $link->url }}"
                                        target="_blank">Link</a></div>
                            </div>
                        </div>
                    @endforeach

                </div>
            </div>
        </div>
    </div>
@endsection

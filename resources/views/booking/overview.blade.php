@extends('layouts.app')

@section('content')
    @if($event)
        <h2>{{ $event->name }} | {{ $filter ? ucfirst($filter) : 'Slot Table' }}</h2>
        <p>
            <a href="{{ route('booking.index',$event) }}" class="btn btn-{{ url()->current() === route('booking.index') || url()->current() === route('booking.index', $event) ? 'success' : 'primary' }}">Show All</a>
            <a href="{{ route('booking.index',$event) }}/departures" class="btn btn-{{ url()->current() === route('booking.index', $event) . '/departures' ? 'success' : 'primary' }}">Show Departures</a>
            <a href="{{ route('booking.index',$event) }}/arrivals" class="btn btn-{{ url()->current() === route('booking.index', $event) . '/arrivals' ? 'success' : 'primary' }}">Show Arrivals</a>
        @if(Auth::check() && Auth::user()->isAdmin && $event->endBooking > now())
                <a href="{{ route('booking.create',$event) }}" class="btn btn-primary"><i class="fa fa-plus"></i> Add
                    Booking</a>
                <a href="{{ route('booking.create',$event) }}/bulk" class="btn btn-primary"><i class="fa fa-plus"></i> Add
                    Timeslots</a>
        @endif
        </p>
        @include('layouts.alert')
        @if($event->startBooking < now() || Auth::check() && Auth::user()->isAdmin)
            Flights available: {{ count($bookings) - count($bookings->where('status',\App\Enums\BookingStatus::BOOKED)) }} / {{ count($bookings) }}
            <table class="table table-hover">
                <thead>
                <tr>
                    <th scope="row">From</th>
                    <th scope="row">To</th>
                    {{--<th scope="row"><abbr title="Calculated Take Off Time">CTOT</abbr></th>--}}
                    {{--<th scope="row"><abbr title="Estimated Time of Arrival">ETA</abbr></th>--}}
                    <th scope="row">Callsign</th>
                    <th scope="row">Aircraft</th>
                    <th scope="row">Book | Available until {{ $event->endBooking->format('d-m-Y H:i') }}z</th>
                    @if(Auth::check() && Auth::user()->isAdmin)
                        <th colspan="3" scope="row">Admin actions</th>
                    @endif
                </tr>
                </thead>
                @foreach($bookings as $booking)
                    {{--Check if flight belongs to the logged in user--}}
                        <tr class="{{ Auth::check() && $booking->user_id == Auth::id() ? 'table-primary' : '' }}">
                            <td>
                                <abbr title="{{ $booking->airportDep->name }}">{{ $booking->dep }}</abbr>
                            </td>
                            <td>
                                <abbr title="{{ $booking->airportArr->name }}">{{ $booking->arr }}</abbr>
                            </td>
                            {{--<td>--}}
                                {{--{{ $booking->ctot }}--}}
                            {{--</td>--}}
                            {{--<td>--}}
                                {{--{{ $booking->eta }}--}}
                            {{--</td>--}}
                            <td>{{ $booking->callsign }}</td>
                            <td>{{ $booking->acType }}</td>
                            <td>
                                {{--Check if booking has been booked--}}
                                @if($booking->status === \App\Enums\BookingStatus::BOOKED)
                                    {{--Check if booking has been booked by current user--}}
                                    @if(Auth::check() && $booking->user_id == Auth::id())
                                        <a href="{{ route('booking.show',$booking) }}" class="btn btn-info">My
                                            booking</a>
                                    @else
                                        <button class="btn btn-dark disabled">
                                            Booked [{{ $booking->user->pic }}]</button>
                                    @endif

                                @elseif($booking->status === \App\Enums\BookingStatus::RESERVED)
                                    {{--Check if a booking has been reserved--}}
                                    @if(Auth::check() && $booking->user_id == Auth::id())
                                        {{--Check if a booking has been reserved by current user--}}
                                        <a href="{{ route('booking.edit',$booking) }}" class="btn btn-info">My
                                            Reservation</a>
                                    @else
                                        <button class="btn btn-dark disabled">
                                            Reserved {{Auth::check() && Auth::user()->isAdmin ? '['.$booking->user->pic .']' : ''}}</button>
                                    @endif
                                @else
                                    @if(Auth::check())
                                        {{--Check if user is logged in--}}
                                        @if($booking->event->startBooking < now() && $booking->event->endBooking > now())
                                            {{--Check if current date is between startBooking and endBooking--}}
                                            <a href="{{ route('booking.edit', $booking) }}" class="btn btn-success">BOOK NOW</a>
                                        @else
                                            <button class="btn btn-danger">Not available</button>
                                        @endif
                                    @else
                                        <a href="{{ route('login') }}" class="btn btn-info">Click here to login</a>
                                    @endif
                                @endif
                            </td>
                            @if(Auth::check() && Auth::user()->isAdmin)
                                <td><a href="{{ route('booking.admin.edit', $booking) }}" class="btn btn-info"><i class="fa fa-edit"></i> Edit</a>
                                </td>
                                <td>
                                    <form action="{{ route('booking.destroy', $booking) }}" method="post">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-danger"><i class="fas fa-trash"></i> Delete</button>
                                    </form>
                                </td>
                                <td>
                                    @if($booking->user)
                                        <a href="mailto:{{ $booking->user->email }}" style="color: white;">
                                            <button class="btn btn-info">
                                                <i class="fas fa-envelope"></i> Send E-mail [{{ $booking->user->email }}]
                                            </button>
                                        </a>
                                    @endif
                                </td>
                            @endif
                        </tr>
                        @endforeach
            </table>
        @else
            Bookings will be available at <strong>{{ $event->startBooking->format('d-m-Y H:i') }}z</strong>
        @endif
    @else
        Currently no events scheduled for booking.
    @endif
@endsection

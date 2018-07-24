@extends('layouts.app')

@section('content')
    @if($event)
        <h2>{{ $event->name }} | Slot Table</h2>
        @if(Auth::check() && Auth::user()->isAdmin)
            <p><a href="{{ route('booking.create',$event->id) }}" class="btn btn-primary"><i class="fa fa-plus"></i> Add
                    Timeslots</a></p>
        @endif
        @include('layouts.alert')
        @if($event->startBooking < \Carbon\Carbon::now() || Auth::check() && Auth::user()->isAdmin)
            Flights available: {{ count($bookings) - count($bookings->where('bookedBy_id',!null)) }} / {{ count($bookings) }}
            <table class="table table-hover">
                <thead>
                <tr>
                    <th>From</th>
                    <th>To</th>
                    <th>CTOT</th>
                    <th>Callsign</th>
                    <th>Aircraft</th>
                    <th>Book | Available until {{ $event->endBooking->format('d-m-Y H:i') }}z</th>
                    @if(Auth::check() && Auth::user()->isAdmin)
                        <th colspan="3">Admin actions</th>
                    @endif
                </tr>
                </thead>
                @foreach($bookings as $booking)
                    {{--Check if flight belongs to the logged in user--}}
                    @if(Auth::check() && isset($booking->bookedBy) && $booking->bookedBy_id == Auth::id() || isset($booking->reservedBy) && $booking->reservedBy_id == Auth::id())
                        <tr class="table-primary">
                    @else
                        <tr>
                            @endif
                            <td>
                                <div data-toggle="tooltip" data-placement="top"
                                     title="{{ $booking->airportDep->name }}">{{ $booking->dep }}</div>
                            </td>
                            <td>
                                <div data-toggle="tooltip" data-placement="top"
                                     title="{{ $booking->airportArr->name }}">{{ $booking->arr }}</div>
                            </td>
                            <td>
                                <div data-toggle="tooltip" data-placement="top"
                                     title="Calculated Take Off Time">{{ $booking->ctot }}</div>
                            </td>
                            <td>{{ $booking->callsign ? $booking->callsign : '-' }}</td>
                            <td>{{ $booking->acType ? $booking->acType : '-' }}</td>
                            <td>
                                {{--Check if booking has been booked--}}
                                @if(isset($booking->bookedBy_id))
                                    {{--Check if booking has been booked by current user--}}
                                    @if(Auth::check() && $booking->bookedBy_id == Auth::id())
                                        <a href="{{ route('booking.show',$booking->id) }}" class="btn btn-info">My
                                            booking</a>
                                    @else
                                        <button class="btn btn-dark disabled">
                                            Booked {{Auth::check() && Auth::user()->isAdmin ? '['.$booking->bookedBy->pic.']' : ''}}</button>
                                    @endif

                                @elseif(isset($booking->reservedBy_id))
                                    {{--Check if a booking has been reserved--}}
                                    @if(Auth::check() && $booking->reservedBy_id == Auth::id())
                                        {{--Check if a booking has been reserved by current user--}}
                                        <a href="{{ route('booking.edit',$booking->id) }}" class="btn btn-info">My
                                            Reservation</a>
                                    @else
                                        <button class="btn btn-dark disabled">
                                            Reserved {{Auth::check() && Auth::user()->isAdmin ? '['.$booking->reservedBy->pic .']' : ''}}</button>
                                    @endif
                                @else
                                    @if(Auth::check() && $booking->event->endBooking > \Carbon\Carbon::now() && !Auth::user()->booked()->where('event_id',$event->id)->first())
                                        {{--Check if user is logged in to generate a clickable link--}}
                                        <a href="{{ route('booking.edit',$booking->id) }}">
                                            @endif
                                            <a href="{{ route('booking.edit', $booking->id) }}" class="btn btn-success">BOOK
                                                NOW</a>
                                            @if(Auth::check() && $booking->event->endBooking > \Carbon\Carbon::now() && !Auth::user()->booked()->where('event_id',$event->id)->first())
                                        </a>
                                    @endif
                                @endif
                            </td>
                            @if(Auth::check() && Auth::user()->isAdmin)
                                <td><a href="{{ route('booking.admin.edit', $booking->id) }}" class="btn btn-info">Edit</a>
                                </td>
                                <td>
                                    <form action="{{ route('booking.destroy', $booking->id) }}" method="post">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-danger">Delete</button>
                                    </form>
                                </td>
                                <td>
                                    @if($booking->bookedBy)
                                        <button class="btn btn-info" href="mailto:{{ $booking->bookedBy->email }}">Send E-mail [{{ $booking->bookedBy->email }}]</button>
                                    @endif
                                </td>
                            @endif
                        </tr>
                        @endforeach
            </table>
        @else
            Bookings will be available at <b>{{ $event->startBooking->format('d-m-Y H:i') }}z</b>
        @endif
    @else
        Currently no events scheduled for booking.
    @endif
@endsection
@extends('layouts.app')

@section('content')

    @php($carbon = new \Carbon\Carbon())

    <h2>{{ Auth::check() && Auth::user()->isAdmin() ? '['. $event->id .']' : '' }} {{ $event->name }} | Slot Table</h2>
    @if(Auth::check() && Auth::user()->isAdmin())
        <p><a href="{{ route('booking.create') }}" class="btn btn-primary"><i class="fa fa-plus"></i> Add new Booking(s)</a></p>
    @endif
    <table class="table table-hover">
        <thead><tr>
            <th>From</th>
            <th>To</th>
            <th>CTOT</th>
            <th>Callsign</th>
            <th>Aircraft</th>
            <th>Book</th>
        </tr></thead>
        @foreach($bookings as $booking)
            <tr>
                <td>{{ $booking->dep }}</td>
                <td>{{ $booking->arr }}</td>
                <td>{{ $carbon->createFromFormat('H:i:s',$booking->ctot)->format('Hi') }}z</td>
                <td>{{ $booking->callsign ? $booking->callsign : '-' }}</td>
                <td>{{ $booking->acType ? $booking->acType : '-' }}</td>
                <td>
                    @if(isset($booking->bookedBy_id))
                        @if(Auth::check() && $booking->bookedBy_id == Auth::id())
                            <a href="{{ route('booking.edit',$booking->id) }}">My booking</a>
                        @else
                            Booked {{Auth::check() && Auth::user()->isAdmin() ? '['.$booking->bookedBy->vatsim_id .']' : ''}}
                        @endif

                    @elseif(isset($booking->reservedBy_id))
                        @if(Auth::check() && $booking->reservedBy_id == Auth::id())
                            <a href="{{ route('booking.edit',$booking->id) }}">My Reservation</a>
                        @else
                            Reserved {{Auth::check() && Auth::user()->isAdmin() ? '['.$booking->reservedBy->vatsim_id .']' : ''}}
                        @endif
                    @else
                        @if(Auth::check())
                            <a href="{{ route('booking.edit',$booking->id) }}">
                        @endif
                        AVAIL
                            @if(Auth::check())
                            </a>
                            @endif
                    @endif
                </td>
            </tr>
        @endforeach
    </table>
@endsection
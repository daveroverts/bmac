@extends('layouts.app')

@section('content')
    <h2>{{ Auth::check() && Auth::user()->isAdmin ? '['. $event->id .']' : '' }} {{ $event->name }} | Slot Table</h2>
    @if(Auth::check() && Auth::user()->isAdmin)
        <p><a href="{{ route('booking.create',$event->id) }}" class="btn btn-primary"><i class="fa fa-plus"></i> Add Timeslots</a></p>
    @endif
    @if(session('message'))
        @component('layouts.alert.danger')
            @slot('title')
                Warning
            @endslot
            {{ session('message') }}
        @endcomponent
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
                <td><div data-toggle="tooltip" data-placement="top" title="{{ $booking->airportDep->name }}">{{ $booking->dep }}</div></td>
                <td><div data-toggle="tooltip" data-placement="top" title="{{ $booking->airportArr->name }}">{{ $booking->arr }}</div></td>
                <td><div data-toggle="tooltip" data-placement="top" title="Calculated Take Off Time">{{ $booking->ctot }}</div></td>
                <td>{{ $booking->callsign ? $booking->callsign : '-' }}</td>
                <td>{{ $booking->acType ? $booking->acType : '-' }}</td>
                <td>
                    {{--Check if booking has been booked--}}
                    @if(isset($booking->bookedBy_id))
                        {{--Check if booking has been booked by current user--}}
                        @if(Auth::check() && $booking->bookedBy_id == Auth::id())
                            <a href="{{ route('booking.show',$booking->id) }}">My booking</a>
                        @else
                            Booked {{Auth::check() && Auth::user()->isAdmin ? '['.$booking->bookedBy->vatsim_id .']' : ''}}
                        @endif

                    @elseif(isset($booking->reservedBy_id))
                        {{--Check if a booking has been reserved--}}
                        @if(Auth::check() && $booking->reservedBy_id == Auth::id())
                            {{--Check if a booking has been reserved by current user--}}
                            <a href="{{ route('booking.edit',$booking->id) }}">My Reservation</a>
                        @else
                            Reserved {{Auth::check() && Auth::user()->isAdmin ? '['.$booking->reservedBy->vatsim_id .']' : ''}}
                        @endif
                    @else
                        @if(Auth::check())
                            {{--Check if user is logged in to generate a clickable link--}}
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
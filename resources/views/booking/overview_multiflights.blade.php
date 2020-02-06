@extends('layouts.app')

@section('content')
    @if($event)
        <h2>{{ $event->name }} | {{ $filter ? ucfirst($filter) : 'Slot Table' }}</h2>
        <p>
            @if($event->hasOrderButtons())
                <a href="{{ route('bookings.event.index',$event) }}"
                   class="btn btn-{{ url()->current() === route('bookings.event.index', $event) || url()->current() === route('bookings.event.index', $event) ? 'success' : 'primary' }}">Show
                    All</a>&nbsp;
                <a href="{{ route('bookings.event.index',$event) }}/departures"
                   class="btn btn-{{ url()->current() === route('bookings.event.index', $event) . '/departures' ? 'success' : 'primary' }}">Show
                    Departures</a>&nbsp;
                <a href="{{ route('bookings.event.index',$event) }}/arrivals"
                   class="btn btn-{{ url()->current() === route('bookings.event.index', $event) . '/arrivals' ? 'success' : 'primary' }}">Show
                    Arrivals</a>&nbsp;
            @endif
            @if(auth()->check() && auth()->user()->isAdmin && $event->endBooking >= now())
                @push('scripts')
                    <script>
                        $('.delete-booking').on('click', function (e) {
                            e.preventDefault();
                            Swal.fire({
                                title: 'Are you sure',
                                text: 'Are you sure you want to remove this booking?',
                                type: 'warning',
                                showCancelButton: true,
                            }).then((result) => {
                                if (result.value) {
                                    Swal.fire('Deleting booking...');
                                    Swal.showLoading();
                                    $(this).closest('form').submit();
                                }
                            });
                        });
                    </script>
                @endpush
                <a href="{{ route('admin.bookings.create',$event) }}" class="btn btn-primary"><i class="fa fa-plus"></i>
                    Add
                    Booking</a>&nbsp;
                <a href="{{ route('admin.bookings.create',$event) }}/bulk" class="btn btn-primary"><i
                        class="fa fa-plus"></i>
                    Add
                    Timeslots</a>&nbsp;
            @endif
        </p>
        @include('layouts.alert')
        @if($event->startBooking <= now() || auth()->check() && auth()->user()->isAdmin)
            Flights available: {{ count($bookings) - count($bookings->where('status',\App\Enums\BookingStatus::BOOKED)) }} / {{ count($bookings) }}
            <table class="table table-hover">
                <thead>
                <tr>
                    <th scope="row">Flight #1</th>
                    <th scope="row">Flight #2</th>
                    <th scope="row">Callsign</th>
                    <th scope="row">Aircraft</th>
                    <th scope="row">Book | Available until {{ $event->endBooking->format('d-m-Y H:i') }}z</th>
                    @if((auth()->check() && auth()->user()->isAdmin) && $event->endEvent >= now())
                        <th colspan="3" scope="row">Admin actions</th>
                    @endif
                </tr>
                </thead>
                @foreach($bookings as $booking)
                    {{--Check if flight belongs to the logged in user--}}
                    <tr class="{{ auth()->check() && $booking->user_id == auth()->id() ? 'table-active' : '' }}">
                        <td>
                            {!! $booking->airportCtot(1) !!}
                        </td>
                        <td>
                            {!! $booking->airportCtot(2) !!}
                        </td>
                        <td class="{{ auth()->check() && auth()->user()->use_monospace_font ? 'text-monospace' : '' }}">{{ $booking->callsign }}</td>
                        <td class="{{ auth()->check() && auth()->user()->use_monospace_font ? 'text-monospace' : '' }}">{{ $booking->acType }}</td>
                        <td>
                            {{--Check if booking has been booked--}}
                            @if($booking->status === \App\Enums\BookingStatus::BOOKED)
                                {{--Check if booking has been booked by current user--}}
                                @if(auth()->check() && $booking->user_id == auth()->id())
                                    <a href="{{ route('bookings.show',$booking) }}" class="btn btn-info">My
                                        booking</a>
                                @else
                                    <button class="btn btn-dark disabled">
                                        Booked [{{ $booking->user->id }}]
                                    </button>
                                @endif

                            @elseif($booking->status === \App\Enums\BookingStatus::RESERVED)
                                {{--Check if a booking has been reserved--}}
                                @can('update', $booking)
                                    {{--Check if a booking has been reserved by current user--}}
                                    <a href="{{ route('bookings.edit',$booking) }}" class="btn btn-info">My
                                        Reservation</a>
                                @else
                                    <button class="btn btn-dark disabled">
                                        Reserved {{auth()->check() && auth()->user()->isAdmin ? '['.$booking->user->pic .']' : ''}}</button>
                                @endcan
                            @else
                                @if(auth()->check())
                                    {{--Check if user is logged in--}}
                                    @if($booking->event->startBooking <= now() && $booking->event->endBooking >= now())
                                        {{--Check if user already has a booking--}}
                                        @if(($booking->event->multiple_bookings_allowed) || (!$booking->event->multiple_bookings_allowed && !auth()->user()->bookings->where('event_id',$event->id)->first()))
                                            {{--Check if user already has a booking, and only 1 is allowed--}}
                                            <a href="{{ route('bookings.edit', $booking) }}" class="btn btn-success">BOOK
                                                NOW</a>
                                        @else
                                            <button class="btn btn-danger">You already have a booking</button>
                                        @endif
                                    @else
                                        <button class="btn btn-danger">Not available</button>
                                    @endif
                                @else
                                    <a href="{{ route('login', ['booking' => $booking]) }}" class="btn btn-info">Click here to
                                        login</a>
                                @endif
                            @endif
                        </td>
                        @if((auth()->check() && auth()->user()->isAdmin) && ($event->endEvent >= now()))
                            <td><a href="{{ route('admin.bookings.edit', $booking) }}" class="btn btn-info"><i
                                        class="fa fa-edit"></i> Edit</a>
                            </td>
                            <td>
                                <form action="{{ route('admin.bookings.destroy', $booking) }}" method="post">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-danger delete-booking"><i class="fas fa-trash"></i> Delete
                                    </button>
                                </form>
                            </td>
                            <td>
                                @if($booking->user_id)
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
            <h3>Bookings will be available at <strong>{{ $event->startBooking->format('d-m-Y H:i') }}z</strong></h3><br>
        @endif
    @else
        Currently no events scheduled for booking.
    @endif
@endsection

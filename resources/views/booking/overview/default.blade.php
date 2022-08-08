<thead>
    <tr>
        <th scope="row">From</th>
        <th scope="row">To</th>
        @if ($event->uses_times)
            <th scope="row"><abbr title="Calculated Take Off Time">CTOT</abbr></th>
            <th scope="row"><abbr title="Estimated Time of Arrival">ETA</abbr></th>
        @endif
        <th scope="row">Callsign</th>
        <th scope="row">Aircraft</th>
        <th scope="row">Book | Available until {{ $event->endBooking->format('d-m-Y H:i') }}z</th>
        @if (auth()->check() && auth()->user()->isAdmin && $event->endEvent >= now())
        <th scope="row">Time booking confirmed</th>
            <th colspan="3" scope="row">Admin actions</th>
        @endif
    </tr>
</thead>
@foreach ($bookings as $booking)
    @php
        $flight = $booking->flights->first();
    @endphp
    {{-- @TODO Temp fix for events using filter buttons --}}
    @if ($flight)
        {{-- Check if flight belongs to the logged in user --}}
        <tr class="{{ auth()->check() && $booking->user_id == auth()->id() ? 'table-active' : '' }}">
            <td>
                {!! $flight->airportDep->fullName !!}
            </td>
            <td>
                {!! $flight->airportArr->fullName !!}
            </td>
            @if ($booking->event->uses_times)
                <td>
                    {{ $flight->formattedCtot }}
                </td>
                <td>
                    {{ $flight->formattedEta }}
                </td>
            @endif
            <td class="{{ auth()->check() && auth()->user()->use_monospace_font ? 'text-monospace' : '' }}">
                {{ $booking->formatted_callsign }}</td>
            <td class="{{ auth()->check() && auth()->user()->use_monospace_font ? 'text-monospace' : '' }}">
                {{ $booking->formatted_actype }}</td>
            <td>
                {{-- Check if booking has been booked --}}
                @if ($booking->status === \App\Enums\BookingStatus::BOOKED)
                    {{-- Check if booking has been booked by current user --}}
                    @if (auth()->check() && $booking->user_id == auth()->id())
                        <a href="{{ route('bookings.show', $booking) }}" class="btn btn-info">My
                            booking</a>
                    @else
                        <button class="btn btn-dark disabled">
                            Booked [{{ $booking->user->id }}]
                        </button>
                    @endif

                @elseif($booking->status === \App\Enums\BookingStatus::RESERVED)
                    {{-- Check if a booking has been reserved --}}
                    @can('update', $booking)
                        {{-- Check if a booking has been reserved by current user --}}
                        <a href="{{ route('bookings.edit', $booking) }}" class="btn btn-info">My
                            Reservation</a>
                    @else
                        <button class="btn btn-dark disabled">
                            Reserved
                            {{ auth()->check() && auth()->user()->isAdmin ? '[' . $booking->user->pic . ']' : '' }}</button>
                    @endcan
                @else
                    @if (auth()->check())
                        {{-- Check if user is logged in --}}
                        @if ($booking->event->startBooking <= now() && $booking->event->endBooking >= now())
                            {{-- Check if user already has a booking --}}
                            @if ($booking->event->multiple_bookings_allowed ||
    (!$booking->event->multiple_bookings_allowed &&
        !auth()->user()->bookings->where('event_id', $event->id)->first()))
                                {{-- Check if user already has a booking, and only 1 is allowed --}}
                                <a href="{{ route('bookings.edit', $booking) }}" class="btn btn-success">BOOK
                                    NOW</a>
                            @else
                                <i class="text-danger">You already have a booking</i>
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
            @if (auth()->check() && auth()->user()->isAdmin && $event->endEvent >= now())
            <td>{{$booking->confirmed_at}}</td>
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
                    @if ($booking->user_id)
                        <a href="mailto:{{ $booking->user->email }}" style="color: white;">
                            <button class="btn btn-info">
                                <i class="fas fa-envelope"></i> Send E-mail [{{ $booking->user->email }}]
                            </button>
                        </a>
                    @endif
                </td>
            @endif
        </tr>
    @endif
@endforeach

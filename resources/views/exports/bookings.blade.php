<table>
    @foreach($bookings as $booking)
        <tr>
            <td>{{ $booking->bookedBy->full_name }}</td>
            <td>{{ $booking->bookedBy_id }}</td>
            <td>{{ $booking->callsign }}</td>
            <td>{{ $booking->dep }}</td>
            <td>{{ $booking->arr }}</td>
            <td>{{ $booking->getOriginal('oceanicFL') }}</td>
            <td>{{ \Carbon\Carbon::parse($booking->getOriginal('ctot'))->format('H:i') }}</td>
            <td>{{ $booking->route }}</td>
        </tr>
    @endforeach
</table>
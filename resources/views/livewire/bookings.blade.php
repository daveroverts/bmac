<div {{ $refreshInSeconds ? "wire:poll.{$refreshInSeconds}s" : '' }}>
    <h3>{{ $event->name }} | {{ $filter ? ucfirst($filter) : 'Slot Table' }}</h3>
    <hr>
    <p>
        @if ($event->hasOrderButtons())
            <button wire:click="setFilter(null)"
                    class="btn {{ !$filter ? 'btn-success' : 'btn-primary' }}">Show
                All
            </button>&nbsp;
            <button wire:click="setFilter('departures')"
                    class="btn {{ $filter === 'departures' ? 'btn-success' : 'btn-primary' }}">Show
                Departures
            </button>&nbsp;
            <button wire:click="setFilter('arrivals')"
                    class="btn {{ $filter === 'arrivals' ? 'btn-success' : 'btn-primary' }}">Show
                Arrivals
            </button>&nbsp;
        @endif
        @if (auth()->check() && auth()->user()->isAdmin && $event->endBooking >= now())
            <a href="{{ route('admin.bookings.create', $event) }}" class="btn btn-primary"><i class="fa fa-plus"></i>
                Add
                Booking</a>&nbsp;
            <a href="{{ route('admin.bookings.create', $event) }}/bulk" class="btn btn-primary"><i
                    class="fa fa-plus"></i>
                Add
                Timeslots</a>&nbsp;
        @endif
    </p>
    @include('layouts.alert')
    @if ($event->startBooking <= now() || (auth()->check() && auth()->user()->isAdmin))
        Flights available: {{ (string) ($total - $booked) }} / {{ $total }}
        <table class="table table-hover table-responsive">
            @if ($event->event_type_id == \App\Enums\EventType::MULTIFLIGHTS->value)
                @include('booking.overview.multiflights')
            @else
                @include('booking.overview.default')
            @endif

        </table>
    @else
        <h3>Bookings will be available at <strong>{{ $event->startBooking->format('d-m-Y H:i') }}z</strong></h3><br>
    @endif
</div>

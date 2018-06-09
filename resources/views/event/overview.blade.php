@extends('layouts.app')

@section('content')

    @php($carbon = new \Carbon\Carbon())

    <h2>Events Overview</h2>
    <p><a href="{{ route('event.create') }}" class="btn btn-primary"><i class="fa fa-plus"></i> Add new Event</a></p>
    <table class="table table-hover">
        <thead><tr>
            <th>ID</th>
            <th>Name</th>
            <th>Date</th>
            <th>Start</th>
            <th>End</th>
            <th>Actions</th>
        </tr></thead>
        @forelse($events as $event)
            <tr>
                <td>{{ $event->id }}</td>
                <td>{{ $event->name }}</td>
                <td>{{ $carbon = $event->startEvent->format('d-m-Y') }}</td>
                <td>{{ $carbon = $event->startEvent->format('Hi') }}z</td>
                <td>{{ $carbon = $event->endEvent->format('Hi') }}z</td>
                <td>
                    <form action="{{ route('event.edit', $event->id) }}">
                        <button class="btn btn-primary"><i class="fa fa-edit"></i> Edit Event</button>
                        @csrf
                    </form>
                </td>
            </tr>
        @empty
            @component('layouts.alert.warning')
                @slot('title')
                    No events found
                @endslot
                No events are in the system, consider adding one, using the button above
            @endcomponent
        @endforelse
    </table>
@endsection
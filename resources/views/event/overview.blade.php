@extends('layouts.app')

@section('content')
    <h2>Events Overview</h2>
    <p><a href="{{ route('event.create') }}" class="btn btn-primary"><i class="fa fa-plus"></i> Add new Event</a></p>
    @include('layouts.alert')
    <table class="table table-hover">
        <thead>
        <tr>
            <th scope="row">ID</th>
            <th scope="row">Name</th>
            <th scope="row">Date</th>
            <th scope="row">Start</th>
            <th scope="row">End</th>
            <th colspan="2">Actions</th>
        </tr>
        </thead>
        @forelse($events as $event)
            <tr>
                <td>{{ $event->id }}</td>
                <td>{{ $event->name }}</td>
                <td>{{ $event->startEvent->format('d-m-Y') }}</td>
                <td>{{ $event->startEvent->format('Hi') }}z</td>
                <td>{{ $event->endEvent->format('Hi') }}z</td>
                <td>
                    <form action="{{ route('event.edit', $event->id) }}">
                        <button class="btn btn-primary"><i class="fa fa-edit"></i> Edit Event</button>
                        @csrf
                    </form>
                </td>
                <td>
                    <form action="{{ route('booking.export', $event->id) }}">
                        <button class="btn btn-success"><i class="fa fa-edit"></i> Export data</button>
                        @csrf
                    </form>
                </td>
            </tr>
        @empty
            @php
                Session::flash('type','info');
                Session::flash('title','No events found');
                Session::flash('message','No events are in the system, consider adding one, using the button above');
            @endphp
            @include('layouts.alert')
        @endforelse
    </table>
@endsection
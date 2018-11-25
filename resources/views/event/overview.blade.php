@extends('layouts.app')

@section('content')
    <h2>Events Overview</h2>
    <p><a href="{{ route('events.create') }}" class="btn btn-primary"><i class="fa fa-plus"></i> Add new Event</a></p>
    @include('layouts.alert')
    <table class="table table-hover">
        <thead>
        <tr>
            <th scope="row">ID</th>
            <th scope="row">Name</th>
            <th scope="row">Type</th>
            <th scope="row">Date</th>
            <th scope="row">Start</th>
            <th scope="row">End</th>
            <th scope="row">Actions</th>
        </tr>
        </thead>
        @forelse($events as $event)
            @if($event->endEvent < now())
                <tr class="table-primary">
            @else
                <tr>
                    @endif
                    <td>{{ $event->id }}</td>
                    <td><a href="{{ route('bookings.event.index', $event) }}" title="Open Slot Table">{{ $event->name }}</a>
                    </td>
                    <td>{{ $event->type->name }}</td>
                    <td>{{ $event->startEvent->format('d-m-Y') }}</td>
                    <td>{{ $event->startEvent->format('Hi') }}z</td>
                    <td>{{ $event->endEvent->format('Hi') }}z</td>
                    <td>
                        <a href="{{ route('events.edit',$event->id) }}" role="button" class="btn btn-primary disabled"><i
                                    class="fa fa-edit"></i> Edit Event</a>&nbsp;
                        @if($event->endEvent > now())
                            <a href="{{ route('bookings.admin.importForm',$event) }}" class="btn btn-success"><i
                                        class="fa fa-edit"></i> Import data</a>&nbsp;
                            <a href="{{ route('bookings.create',$event) }}" class="btn btn-primary"><i
                                        class="fa fa-plus"></i> Add Timeslots</a>&nbsp;
                            <a href="{{ route('bookings.admin.autoAssignForm',$event) }}" class="btn btn-primary">Auto
                                Assign FL / Route</a>&nbsp;
                            @if($event->is_oceanic_event)
                                <a href="{{ route('bookings.admin.autoAssignForm',$event) }}" class="btn btn-primary">
                                    Auto Assign FL / Route</a>&nbsp;
                            @endif
                        @endif
                        <a href="{{ route('events.email.form',$event) }}" class="btn btn-primary"><i
                                    class="fa fa-envelope"></i> Send E-mails (all persons)</a>&nbsp;
                        <a href="{{ route('bookings.export',$event) }}" class="btn btn-success"><i
                                    class="fa fa-edit"></i> Export data</a>&nbsp;
                    </td>
                </tr>
                @empty
                    @php
                        flashMessage('info', 'No events found', 'No events are in the system, consider adding one, using the button above');
                    @endphp
                    @include('layouts.alert')
                @endforelse
                {{ $events->links() }}
    </table>
    {{ $events->links() }}
@endsection

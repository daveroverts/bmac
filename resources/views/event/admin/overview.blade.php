@extends('layouts.app')

@section('content')
    <h2>Events Overview</h2>
    <p><a href="{{ route('admin.events.create') }}" class="btn btn-primary"><i class="fa fa-plus"></i> Add new Event</a>
    </p>
    @include('layouts.alert')
    @push('scripts')
        <script>
            $('.delete-event').on('click', function (e) {
                e.preventDefault();
                Swal.fire({
                    title: 'Are you sure',
                    text: 'Are you sure you want to remove this event?',
                    type: 'warning',
                    showCancelButton: true,
                }).then((result) => {
                    if (result.value) {
                        Swal.fire('Deleting event...');
                        Swal.showLoading();
                        $(this).closest('form').submit();
                    }
                });
            });
        </script>
    @endpush
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
                <tr class="table-active">
            @else
                <tr>
                    @endif
                    <td>{{ $event->id }}</td>
                    <td><a href="{{ route('admin.events.show', $event) }}">{{ $event->name }}</a>
                    </td>
                    <td>{{ $event->type->name }}</td>
                    <td>{{ $event->startEvent->format('d-m-Y') }}</td>
                    <td>{{ $event->startEvent->format('Hi') }}z</td>
                    <td>{{ $event->endEvent->format('Hi') }}z</td>
                    <td>
                        <a href="{{ route('admin.events.edit',$event) }}" role="button" class="btn btn-primary"><i
                                class="fa fa-edit"></i> Edit Event</a>&nbsp;
                        @if($event->endEvent > now())
                            <a href="{{ route('admin.bookings.importForm',$event) }}" class="btn btn-success"><i
                                    class="fa fa-edit"></i> Import data</a>&nbsp;
                            <a href="{{ route('admin.bookings.create', $event) }}/bulk" class="btn btn-primary"><i
                                    class="fa fa-plus"></i> Add Timeslots</a>&nbsp;
                            @if($event->is_oceanic_event)
                                <a href="{{ route('admin.bookings.autoAssignForm',$event) }}" class="btn btn-primary">
                                    Auto Assign FL / Route</a>&nbsp;
                            @endif
                        @endif
                        <a href="{{ route('admin.events.email.form',$event) }}" class="btn btn-primary"><i
                                class="fa fa-envelope"></i> Send E-mails (all persons)</a>&nbsp;
                        <a href="{{ route('admin.bookings.export',$event) }}" class="btn btn-success"><i
                                class="fa fa-edit"></i> Export data</a>&nbsp;
                        @if($event->event_type_id == \App\Enums\EventType::MULTIFLIGHTS)
                            <a href="{{ route('admin.bookings.export',[$event, 'vacc']) }}" class="btn btn-success"><i
                                    class="fa fa-edit"></i> Export data + VACC data</a>&nbsp;
                            <a href="{{ route('admin.bookings.routeAssignForm', $event) }}" class="btn btn-primary"><i
                                    class="fa fa-edit"></i> Assign Routes</a>&nbsp;
                        @endif
                        @if($event->startEvent > now())
                            <form action="{{ route('admin.events.destroy', $event) }}" method="post">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-danger delete-event">Delete Event</button>
                            </form>
                        @endif
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
@extends('layouts.app')

@section('content')
    <h3>{{ __('Events Overview') }}</h3>
    <hr>
    <p><a href="{{ route('admin.events.create') }}" class="btn btn-primary"><i class="fa fa-plus"></i>
            {{ __('Add new Event') }}</a>
    </p>
    @include('layouts.alert')
    <table class="table table-hover table-responsive">
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
            @if ($event->endEvent < now())
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
                <div class="d-flex flex-row flex-wrap justify-content-center justify-content-md-start">
                    <a href="{{ route('admin.events.edit', $event) }}" role="button" class="btn btn-primary m-1"><i
                            class="fa fa-edit"></i> {{ __('Edit') }}</a>&nbsp;
                    @if ($event->endEvent > now())
                        <a href="{{ route('admin.bookings.importForm', $event) }}" class="btn btn-success m-1"><i
                                class="fa fa-file-import"></i> {{ __('Import data') }}</a>&nbsp;
                        <a href="{{ route('admin.bookings.create', $event) }}/bulk" class="btn btn-primary m-1"><i
                                class="fa fa-plus"></i> {{ __('Add Timeslots') }}</a>&nbsp;
                        @if ($event->is_oceanic_event)
                            <a href="{{ route('admin.bookings.autoAssignForm', $event) }}" class="btn btn-primary m-1">
                                {{ __('Auto Assign FL / Route') }}</a>&nbsp;
                        @endif
                    @endif
                    <a href="{{ route('admin.events.email.form', $event) }}" class="btn btn-primary m-1"><i
                            class="fa fa-envelope"></i> {{ __('Send mail to all') }}</a>&nbsp;

                    <button class="btn btn-success dropdown-toggle m-1" type="button" data-bs-toggle="dropdown"
                        aria-haspopup="true" aria-expanded="false"><i class="fa fa-file-export"></i> Export</button>
                    <div class="dropdown-menu">
                        <a class="dropdown-item"
                            href="{{ route('admin.bookings.export', $event) }}">{{ __('Excluding emails') }}</a>
                        @if ($event->event_type_id == \App\Enums\EventType::MULTIFLIGHTS->value)
                            <a class="dropdown-item" href="{{ route('admin.bookings.export', [$event, 'vacc']) }}">
                                {{ __('Including emails') }}</a>
                        @endif
                    </div>

                    @if ($event->event_type_id == \App\Enums\EventType::MULTIFLIGHTS->value)
                        <a href="{{ route('admin.bookings.routeAssignForm', $event) }}" class="btn btn-primary m-1"><i
                                class="fa fa-edit"></i> {{ __('Assign Routes') }}</a>&nbsp;
                    @endif
                    @if ($event->startEvent > now())
                        <x-confirm-button
                            class="m-1"
                            confirm-text="Are you sure you want to delete all bookings?"
                            loading-message="Deleting bookings..."
                            form="delete-bookings-{{ $event->id }}"
                        >{{ __('Delete bookings') }}</x-confirm-button>
                        <x-confirm-button
                            class="m-1"
                            confirm-text="Are you sure you want to remove this event?"
                            loading-message="Deleting event..."
                            form="delete-event-{{ $event->id }}"
                        >{{ __('Delete event') }}</x-confirm-button>
                    @endif
                </div>
            </td>
            </tr>

            <x-form :action="route('admin.events.delete-bookings', $event)" id="delete-bookings-{{ $event->id }}" method="DELETE"
                style="display: none;"></x-form>
            <x-form :action="route('admin.events.destroy', $event)" id="delete-event-{{ $event->id }}" method="DELETE"
                style="display: none;"></x-form>
        @empty
            @php
                flashMessage(
                    'info',
                    'No events found',
                    'No events are in the system, consider adding one, using the button above',
                );
            @endphp
            @include('layouts.alert')
        @endforelse
        {{ $events->links() }}
    </table>
    {{ $events->links() }}
@endsection

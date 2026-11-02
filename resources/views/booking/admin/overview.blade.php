@extends('layouts.app')

@section('content')
    <div class="d-flex flex-wrap gap-2 justify-content-between align-items-center">
        <h3 class="mb-0" style="text-wrap: balance;">{{ $event->name }} | {{ __('Bookings') }}</h3>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('events.bookings.index', $event) }}" class="btn btn-outline-primary">{{ __('View slot table') }}</a>
            @if ($event->endBooking >= now() && $event->allowsManualBookings())
                <a href="{{ route('admin.events.bookings.create', $event) }}" class="btn btn-outline-primary"><i class="fa fa-plus"></i> {{ __('Add Booking') }}</a>
                <a href="{{ route('admin.events.bookings.create', $event) }}?bulk=1" class="btn btn-primary"><i class="fa fa-plus"></i> {{ __('Add Timeslots') }}</a>
            @endif
            <a href="{{ route('admin.events.emails.bulk.create', $event) }}" class="btn btn-outline-primary"><i class="fa fa-envelope"></i> {{ __('Send mail to all') }}</a>
            <div class="dropdown">
                <button class="btn btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false"><i class="fa fa-file-export"></i> {{ $event->endEvent > now() ? __('Import / Export') : __('Export') }}</button>
                <div class="dropdown-menu dropdown-menu-end">
                    @if ($event->endEvent > now())
                        <a class="dropdown-item" href="{{ route('admin.events.bookings.import.create', $event) }}">{{ __('Import data') }}</a>
                        <div class="dropdown-divider"></div>
                    @endif
                    <a class="dropdown-item" href="{{ route('admin.events.bookings.export', $event) }}">{{ __('Bookings (CSV)') }}</a>
                    <a class="dropdown-item" href="{{ route('admin.events.bookings.export', [$event, 'format' => 'vatcan-csv']) }}">{{ __('VATCAN slots (CSV)') }}</a>
                    <a class="dropdown-item" href="{{ route('admin.events.bookings.export', [$event, 'format' => 'vatcan-json']) }}">{{ __('VATCAN slots (JSON)') }}</a>
                </div>
            </div>
        </div>
    </div>
    <hr>
    @include('layouts.alert')
    <livewire:booking.admin.overview :event="$event" />
@endsection

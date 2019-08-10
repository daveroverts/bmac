@extends('layouts.app')

@section('content')
    @include('layouts.alert')
    @if($event)
        <h3>Welcome to the {{ $event->name }} – {{ $event->startEvent->toFormattedDateString() }}</h3>
        <a href="{{ route('bookings.event.index',$event) }}" class="btn btn-primary">Open Booking Table</a>
        @if($event->image_url)
            <img src="{{ $event->image_url }}" class="img-fluid rounded">
        @endif
        {!! $event->description !!}
    @else
        Currently no events scheduled.
    @endif
@endsection

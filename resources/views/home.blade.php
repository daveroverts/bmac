@extends('layouts.app')

@section('content')
    @include('layouts.alert')
    @forelse($events as $event)
        <h3>Welcome to the {{ $event->name }} – {{ $event->startEvent->toFormattedDateString() }}</h3>
        @if($event->image_url)
            <img src="{{ $event->image_url }}" class="img-fluid rounded">
        @endif
        {!! $event->description !!}
        <div class="text-center"><a href="{{ route('bookings.event.index',$event) }}" class="btn btn-primary">Open Booking Table</a></div>
        <hr>
    @empty
        Currently no events scheduled.
    @endforelse
@endsection

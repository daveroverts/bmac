@extends('layouts.app')

@section('content')
    @include('layouts.alert')
    <h3>Upcoming Events</h3>
    <hr>

    @forelse($events as $event)
    <div class="row event">
        <div class="col-xl-6 col-lg-6 col-md-12 col-sm-12">
            <h4 class="event-title text-primary"><a href="{{ route('bookings.event.index',$event) }}">{{ $event->name }}</a></h4>
            <p><i class="fas fa-calendar text-primary"></i>&nbsp;&nbsp;{{ $event->startEvent->toFormattedDateString() }}<br>
            <i class="fas fa-clock text-primary"></i>&nbsp;&nbsp;{{ $event->startEvent->format('H:i\z') }} - {{ $event->endEvent->format('H:i\z') }}</p>
            {!! $event->description !!}
            <a href="{{ route('bookings.event.index',$event) }}" class="btn btn-success">See Available Slots</a>
        </div>
        <div class="col-xl-6 col-lg-6 col-md-12 col-sm-12">
            @if($event->image_url)
                <a href="{{ route('bookings.event.index',$event) }}"><img src="{{ $event->image_url }}" class="img-fluid rounded"></a>
            @endif    
        </div>
    </div>
    <hr>
    @empty
        Currently no events scheduled.
    @endforelse
@endsection

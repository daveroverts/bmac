@extends('layouts.app')

@section('content')
    @include('layouts.alert')
    @if($event)
        <h3>Welcome to {{ $event->name }} – {{ $event->startEvent->toFormattedDateString() }}</h3>
        @include('layouts.alert')
        @if($event->image_url)
            <img src="{{ $event->image_url }}">
        @endif
        {!! $event->description !!}
    @else
        Currently no events scheduled.
    @endif
@endsection

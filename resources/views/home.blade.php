@extends('layouts.app')

@section('content')
    @if($event)
        <h3>Welcome to {{ $event->name }} – {{ $event->startEvent->toFormattedDateString() }}</h3>
        @include('layouts.alert')
        {!! $event->description !!}
    @else
        Currently no events scheduled.
    @endif
@endsection

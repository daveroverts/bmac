@extends('layouts.app')

@section('content')

    @foreach(nextEvents() as $event)
        @if($event->faqs->isNotEmpty())
            <h4>FAQ for {{ $event->name }}</h4>
            <hr>
            @foreach($event->faqs as $faq)
                <p>
                    <strong>{{ $faq->question }}</strong>
                    <br>
                    {!! $faq->answer !!}
                </p>
                <hr>
            @endforeach
        @endif
    @endforeach

    <h4>General FAQ</h4>
    @forelse($faqs as $faq)
        <p>
            <strong>{{ $faq->question }}</strong>
            <br>
            {!! $faq->answer !!}
        </p>
        <hr>
    @empty
        <p>No Questions / Answers are available at the moment</p>
    @endforelse
@endsection

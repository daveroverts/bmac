@extends('layouts.app')

@section('content')
    @foreach (nextEventsForFaq() as $event)
        @if ($event->faqs->isNotEmpty())
            <h3>FAQ for {{ $event->name }}</h3>
            <hr>
            @foreach ($event->faqs as $faq)
                <p>
                    <strong>{{ $faq->question }}</strong>
                    <br>
                    {!! $faq->answer !!}
                </p>
                <hr>
            @endforeach
        @endif
    @endforeach

    <h3>General FAQ</h3>
    <hr>
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

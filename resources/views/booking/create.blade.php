@extends('layouts.app')

@section('content')
    @if (count($errors) > 0)
        <div class = "alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ $event->name }} | Add Booking(s)</div>

                <div class="card-body">
                    <form method="POST" action="{{ route('booking.create',$event->id) }}">
                        @csrf

                        {{--Event--}}
                        <div class="form-group row">
                            <label for="event" class="col-md-4 col-form-label text-md-right">Event</label>

                            <div class="col-md-6">
                                <div class="form-control-plaintext">{{ $event->name }}</div>

                            </div>
                        </div>

                        {{--Date--}}
                        <div class="form-group row">
                            <label for="date" class="col-md-4 col-form-label text-md-right">Date</label>

                            <div class="col-md-6">
                                <div class="form-control-plaintext">{{ $event->startEvent->format('d-m-Y') }}</div>

                            </div>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

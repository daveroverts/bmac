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
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

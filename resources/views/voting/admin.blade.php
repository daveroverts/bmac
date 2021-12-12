@extends('layouts.app')

@section('content')
    <h3>Voting Admin</h3>
    <hr>
    <p><a href="{{route("admin.voting.addnew")}}" class="btn btn-primary"><i class="fa fa-plus"></i>  Add new Poll</a></p>
    @include('layouts.alert')
    <table class="table table-hover table-responsive">
        <thead>
        <tr>
            <th scope="row">Poll ID</th>
            <th scope="row">Name</th>
            <th scope="row">Description</th>
            <th scope="row">Status</th>
            <th scope="row">Action</th>
        </tr>
        {{-- <tr><td>1</td><td>Poll 1</td><td>Shown</td><td><a class="btn btn-secondary" href="#">View More</a></td></tr>--}}
        @foreach ($polls as $poll)
            <tr><td>{{$poll->id}}</td><td>{{$poll->poll_name}}</td><td>{{$poll->poll_description}}</td><td>{{$poll->hidden == 0 ? "Visible" : "Hidden"}}</td><td><a class="btn btn-secondary" href="{{route("admin.voting.viewpoll",['poll_id' => $poll->id])}}">View More</a></td></tr>
        @endforeach
        </thead>
    </table>
@endsection

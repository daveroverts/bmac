@extends('layouts.app')

@section('content')
    @include('layouts.alert')
    <h3>Airfield Voting</h3>
    <p>Please do note that you are only allowed to vote for the top three airports for each category <b>ONCE</b>. Please vote wisely.</p>

    @foreach($data as $poll)

            <div class="card">








    <h4 class="card-header">
        {{$poll['poll_name']}}

    </h4>
            @if($poll['votes']->first())
                <div class="card-body">
                    <h4 class="card-title">{{$poll['poll_description']}}</h4>
                    <p>You have already voted. Here is what you voted:</p>
                    <ul class="list-group">
                        @foreach($poll['votes'] as $vote)
                        <li class="list-group-item">{{$loop->index+1}}. {{$vote->name}}</li>
                        @endforeach
                    </ul>
                </div>
            @else

            <div class="card-body">
                <form method="POST" action="{{route('voting.vote')}}">
                    <input type="hidden" name="poll_id" value="{{$poll['poll_id']}}">
                    @csrf
                <h4 class="card-title">{{$poll['poll_description']}}</h4>
    <div class="row">

        <div class="col">
            First Choice:
            <select name="first" class="form-control">
                @foreach($poll['poll_options'] as $option)
                <option value="{{$option['option_id']}}">{{$option['option_name']}}</option>
                @endforeach
            </select>
        </div>
        <div class="col">
            Second Choice:
            <select name="second" class="form-control">
                @foreach($poll['poll_options'] as $option)
                    <option value="{{$option['option_id']}}">{{$option['option_name']}}</option>
                @endforeach
            </select>

        </div>
        <div class="col">
            Third Choice:
            <select name="third" class="form-control">
                @foreach($poll['poll_options'] as $option)
                    <option value="{{$option['option_id']}}">{{$option['option_name']}}</option>
                @endforeach
            </select>


        </div>
    </div>
<div class="row mt-2" style="float:right;">
    <input type="submit" value="Submit Vote" name="submit" class="btn btn-primary">

</div>
                </form>
        </div>
        </div>

        @endif
    @endforeach

@endsection

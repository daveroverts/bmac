@extends('layouts.app')

@section('content')
    @include('layouts.alert')
    <h3>Viewing Poll {{$poll->poll_name}}</h3>
    <form method="POST" action="{{route('admin.voting.editpoll')}}">
        @csrf
        <input type="hidden" name="poll_id" value="{{$poll->id}}">
    <div class="form-group">
        <label for="pollName">Poll Name</label>
        <input type="value" class="form-control" value="{{$poll->poll_name}}" disabled>
    </div>
        <div class="form-group">
            <label for="pollName">Poll Description</label>
            <texarea class="form-control" rows="3" disabled>
                {{$poll->poll_description}}
            </texarea>
        </div>
        <div class="form-group">
            <label for="pollVisibility">Poll Visibility:</label>
            <select class="form-control form-select" name="hidden">
                <option value="0" {{$poll->hidden =="0" ? "selected": ""}}>Visible</option>
                <option value="1" {{$poll->hidden =="1" ? "selected": ""}}>Hidden</option>
            </select>
            <input type="submit" class="form-control btn btn-primary" value="Update">
        </div>
        <div class="form-group">
            <label for="pollOptions">Poll Options</label>
            <ul>
                @foreach($poll_choices as $poll_option)
                <li>{{$poll_option->option_name}}</li>
                    @endforeach
            </ul>
        </div>
        <div class="form-group">
            <p>Statistics:</p>
            @if (!$votes->isEmpty())
                <table class="table table-hover table-responsive">
                    <tr><td>Vote ID</td><td>Vote Choice</td><td>Order</td><td>Vote Count</td></tr>
            @foreach($votes as $vote)
                    <tr>
                        <td>{{$vote->id}}</td>
                        <td>{{$vote->name}}</td>
                        <td>{{$vote->priority == 0 ? "First Choice" : ($vote->priority == 1 ? "Second Choice": "Third Choice") }}</td>
                        <td>{{$vote->votes}}</td>
                    </tr>
                @endforeach
                </table>
            @else
                Nothing to show for now :(
            @endif
        </div>
    </form>
@endsection

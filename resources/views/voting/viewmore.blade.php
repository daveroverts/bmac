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
            @if (count($votes))
                @push('scripts')
                <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
                <script type="text/javascript">
                    google.charts.load('current', {'packages':['bar']});
                    google.charts.setOnLoadCallback(drawChart);

                    function drawChart() {
                        var data = google.visualization.arrayToDataTable([
                            ['Airport', 'First', 'Second', 'Third'],
                                @foreach($votes as $vote)
                                @if(!$loop->last)
                            ['{{$vote->name}}', {{$vote->first}}, {{$vote->second}}, {{$vote->third}}],
                            @else
                            ['{{$vote->name}}', {{$vote->first}}, {{$vote->second}}, {{$vote->third}}]
                            @endif
                            @endforeach
                        ]);

                        var options = {
                            chart: {
                                title: '{{$poll->poll_name}}',
                                subtitle: 'First, Second, Third choices for each airport',
                            }
                        };

                        var chart = new google.charts.Bar(document.getElementById('columnchart_material'));

                        chart.draw(data, google.charts.Bar.convertOptions(options));
                    }
                </script>
                @endpush

                <div id="columnchart_material" style="width: 1000px; height: 500px;"></div>

            @else
                Nothing to show for now :(
            @endif
        </div>
    </form>
@endsection

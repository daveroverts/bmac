@extends('layouts.app')

@section('content')
    <h3>Adding New Poll</h3>
    <hr>
    @include('layouts.alert')
    @push('scripts')
        <script>
            $(document).ready(function() {
                var max_fields = 10;
                var wrapper = $(".option_container");
                var add_button = $(".add_form_field");

                var x = 1;
                $(add_button).click(function(e) {
                    e.preventDefault();
                    if (x < max_fields) {
                        x++;
                        $(wrapper).append('<div class="row form-group"><div class="col"><input type="text" class="form-control" placeholder="Type option here..." name="option[]"/></div><div class="col"><a href="#" class="delete btn btn-secondary">Delete</a></div></div>');
                    } else {
                        alert('You Reached the limits')
                    }
                });

                $(wrapper).on("click", ".delete", function(e) {
                    e.preventDefault();
                    $(this).parent('div').parent('div').remove();
                    x--;
                })
            });
        </script>
    @endpush
    <form method="POST" action="{{route("admin.voting.addpoll")}}">
        @csrf
        <div class="form-group">
            <label for="pollName">Poll Name</label>
            <input type="value" class="form-control" placeholder="Enter the name of the Poll" name="name">
        </div>
        <div class="form-group">
            <label for="pollDescription">Poll Description</label>
            <textarea class="form-control" rows="3" name="description" placeholder="Description of Poll">

            </textarea>
        </div>
        <hr>
        <h4>Poll Options</h4>
        <div class="option_container">
            <a class="btn btn-primary add_form_field"><i class="fa fa-plus"></i>  Add New Field</a>
            <div class="form-group row">
                <div class="col">
                <input type="text" name="option[]" class="form-control" placeholder="Type option here...">
                </div>
                <div class="col"></div>
            </div>
        </div>
<div class="form-group">
    <input type="submit" class="btn btn-primary" value="Submit">
</div>
    </form>
@endsection

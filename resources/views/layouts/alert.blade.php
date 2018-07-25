@if(session('message'))
    <div class="alert alert-dismissable alert-{{ session('type') }}" role="alert">
        <button type="button" class="close" data-dismiss="alert">&times;</button>
        <div class="alert-title"><strong>{{ session('title') }}</strong></div>
        {{ session('message') }}
    </div>
@endif
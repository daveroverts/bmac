@if(session('message'))
    <div class="alert alert-{{ session('type') }}" role="alert">
        <div class="alert-title"><strong>{{ session('title') }}</strong></div>
        {{ session('message') }}
    </div>
@endif
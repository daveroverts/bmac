@extends('layouts.app')

@section('content')
    <h3>{{ __('Events Overview') }}</h3>
    <hr>
    <p><a href="{{ route('admin.events.create') }}" class="btn btn-primary"><i class="fa fa-plus"></i>
            {{ __('Add new Event') }}</a>
    </p>
    @include('layouts.alert')
    <livewire:event.admin.overview />
@endsection

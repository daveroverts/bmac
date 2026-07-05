@extends('layouts.app')

@section('content')
    <h3>Airports Overview</h3>
    <hr>
    @include('layouts.alert')
    <div class="d-flex flex-row flex-wrap">
        <a href="{{ route('admin.airports.create') }}" class="btn btn-primary m-1"><i class="fa fa-plus"></i> Add new
            Airport</a>
        <a href="{{ route('admin.airportLinks.create') }}" class="btn btn-primary m-1"><i class="fa fa-plus"></i> Add
            new
            Airport
            Link</a>
        <x-confirm-button
            class="m-1"
            confirm-text="Are you sure you want to delete all unused airports?"
            loading-message="Deleting unused airports..."
            form="delete-unused-airports"
        ><i class="fa fa-trash"></i> Delete unused airports</x-confirm-button>
    </div>
    <livewire:airport.admin.overview />
    <x-form :action="route('admin.airports.unused.destroy')" id="delete-unused-airports" method="DELETE" style="display: none;"></x-form>
@endsection

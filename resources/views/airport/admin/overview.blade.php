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
    <table class="table table-hover">
        <thead>
            <tr>
                <th scope="row">ICAO</th>
                <th scope="row">IATA</th>
                <th scope="row">Name</th>
                <th scope="row" colspan="2">Actions</th>
            </tr>
        </thead>
        @foreach ($airports as $airport)
            <tr>
                <td><a href="{{ route('admin.airports.show', $airport) }}">{{ $airport->icao }}</a></td>
                <td><a href="{{ route('admin.airports.show', $airport) }}">{{ $airport->iata }}</a></td>
                <td><a href="{{ route('admin.airports.show', $airport) }}">{{ $airport->name }}</a></td>
                <td>
                    <a href="{{ route('admin.airports.edit', $airport) }}">
                        <button class="btn btn-primary">
                            <i class="fa fa-edit"></i> Edit Airport
                        </button>
                    </a>
                </td>
                <td>
                    @if (
                        $airport->flightsDep->isEmpty() &&
                            $airport->flightsArr->isEmpty() &&
                            $airport->eventDep->isEmpty() &&
                            $airport->eventArr->isEmpty())
                        <form action="{{ route('admin.airports.destroy', $airport) }}" method="post">
                            @method('DELETE')
                            <x-confirm-button
                                confirm-text="Are you sure you want to remove this airport?"
                                loading-message="Deleting airport..."
                            ><i class="fa fa-trash"></i> Remove Airport</x-confirm-button>
                            @csrf
                        </form>
                    @else
                        <button class="btn btn-danger disabled" disabled><i class="fa fa-trash"></i> Remove Airport
                        </button>
                    @endif
                </td>
            </tr>
        @endforeach
        {{ $airports->links() }}
    </table>
    {{ $airports->links() }}
    <x-form :action="route('admin.airports.destroyUnused')" id="delete-unused-airports" method="POST" style="display: none;"></x-form>
@endsection

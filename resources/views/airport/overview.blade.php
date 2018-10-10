@extends('layouts.app')

@section('content')
    <h2>Airports Overview</h2>
    @include('layouts.alert')
    <p>
        <a href="{{ route('airport.create') }}" class="btn btn-primary"><i class="fa fa-plus"></i> Add new Airport</a>
        <a href=" {{ route('airportLink.create') }}" class="btn btn-primary"><i class="fa fa-plus"></i> Add new Airport
            Link</a>
    </p>
    <table class="table table-hover">
        <thead>
        <tr>
            <th scope="row">ICAO</th>
            <th scope="row">IATA</th>
            <th scope="row">Name</th>
            <th scope="row" colspan="2">Actions</th>
        </tr>
        </thead>
        @forelse($airports as $airport)
            <tr>
                <td><a href="{{ route('airport.show', $airport) }}">{{ $airport->icao }}</a></td>
                <td><a href="{{ route('airport.show', $airport) }}">{{ $airport->iata }}</a></td>
                <td><a href="{{ route('airport.show', $airport) }}">{{ $airport->name }}</a></td>
                <td>
                    <a href="{{ route('airport.edit', $airport) }}">
                        <button class="btn btn-primary">
                            <i class="fa fa-edit"></i> Edit Airport
                        </button>
                    </a>
                </td>
                <td>
                    @if($airport->bookingsDep->isEmpty() && $airport->bookingsArr->isEmpty())
                        <form action="{{ route('airport.destroy', $airport) }}" method="post">
                            @method('DELETE')
                            <button class="btn btn-danger"><i class="fa fa-trash"></i> Remove Airport</button>
                            @csrf
                        </form>
                    @else
                        <button class="btn btn-danger disabled" disabled><i class="fa fa-trash"></i> Remove Airport</button>
                    @endif
                </td>
            </tr>
        @empty
            @component('layouts.alert.warning')
                @slot('title')
                    No airports found
                @endslot
                No airports are in the system, consider adding one, using the button above
            @endcomponent
        @endforelse
        {{ $airports->links() }}
    </table>
    {{ $airports->links() }}
@endsection
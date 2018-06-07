@extends('layouts.app')

@section('content')
    <h2>Airports Overview</h2>
    <p><a href="{{ route('airport.create') }}" class="btn btn-primary"><i class="fa fa-plus"></i> Add new Airport</a></p>
    <table class="table table-hover">
        <thead><tr>
            <th>ICAO</th>
            <th>IATA</th>
            <th>Edit (doesn't work at this time)</th>
        </tr></thead>
        @forelse($airports as $airport)
            <tr>
                <td>{{ $airport->icao }}</td>
                <td>{{ $airport->iata }}</td>
                <td>
                    <form action="{{ route('airport.edit', $airport->icao) }}">
                        <button class="btn btn-primary" disabled><i class="fa fa-edit"></i> Edit Airport</button>
                        @csrf
                    </form>
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
    </table>
@endsection
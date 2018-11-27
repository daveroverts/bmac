@extends('layouts.app')

@section('content')
    <h2>Airport Links Overview</h2>
    @include('layouts.alert')
    <p>
        <a href=" {{ route('airportLinks.create') }}" class="btn btn-primary"><i class="fa fa-plus"></i> Add new Airport
            Link</a>
    </p>
    <table class="table table-hover">
        <thead>
        <tr>
            <th scope="row">Airport</th>
            <th scope="row">Type / Name</th>
            <th scope="row" colspan="2">Actions</th>
        </tr>
        </thead>
        @forelse($airportLinks as $airportLink)
            <tr>
                <td><a href="{{ route('airports.show', $airportLink->airport) }}">{{ $airportLink->airport->name }} [{{ $airportLink->airport->icao }} | {{ $airportLink->airport->iata }}]</a></td>
                <td><a href="{{ $airportLink->url }}">{{ $airportLink->name ?? $airportLink->type->name }}</a></td>
                <td>
                    <a href="{{ route('airportLinks.edit', $airportLink) }}">
                        <button class="btn btn-primary">
                            <i class="fa fa-edit"></i> Edit Airport Link
                        </button>
                    </a>
                </td>
                <td>
                    <form action="{{ route('airportLinks.destroy', $airportLink) }}" method="post">
                        @method('DELETE')
                        <button class="btn btn-danger"><i class="fa fa-trash"></i> Remove Airport Link</button>
                        @csrf
                    </form>
                </td>
            </tr>
        @empty
            @php
                flashMessage('warning', 'No airport links found', 'No airport links are in the system, consider adding one, using the button above');
            @endphp
            @include('layouts.alert')
        @endforelse
        {{ $airportLinks->links() }}
    </table>
    {{ $airportLinks->links() }}
@endsection

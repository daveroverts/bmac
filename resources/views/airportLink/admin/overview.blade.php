@extends('layouts.app')

@section('content')
    <h2>Airport Links Overview</h2>
    @include('layouts.alert')
    <p>
        <a href=" {{ route('admin.airportLinks.create') }}" class="btn btn-primary"><i class="fa fa-plus"></i> Add new
            Airport
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
        @foreach ($airportLinks as $airportLink)
            <tr>
                <td>
                    <a href="{{ route('admin.airports.show', $airportLink->airport) }}">{{ $airportLink->airport->name }}
                        [{{ $airportLink->airport->icao }} | {{ $airportLink->airport->iata }}]</a>
                </td>
                <td><a href="{{ $airportLink->url }}">{{ $airportLink->name ?? $airportLink->type->name }}</a></td>
                <td>
                    <a href="{{ route('admin.airportLinks.edit', $airportLink) }}">
                        <button class="btn btn-primary">
                            <i class="fa fa-edit"></i> Edit Airport Link
                        </button>
                    </a>
                </td>
                <td>
                    <form action="{{ route('admin.airportLinks.destroy', $airportLink) }}" method="post">
                        @method('DELETE')
                        <x-confirm-button
                            confirm-text="Are you sure you want to remove this airport link?"
                            loading-message="Deleting airport link..."
                        ><i class="fa fa-trash"></i> Remove Airport Link</x-confirm-button>
                        @csrf
                    </form>
                </td>
            </tr>
        @endforeach
        {{ $airportLinks->links() }}
    </table>
    {{ $airportLinks->links() }}
@endsection

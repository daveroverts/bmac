@extends('layouts.app')

@section('content')
    <h3>Airports Overview</h3>
    <hr>
    @include('layouts.alert')
    @push('scripts')
        <script>
            $('.delete-airport').on('click', function (e) {
                e.preventDefault();
                Swal.fire({
                    title: 'Are you sure',
                    text: 'Are you sure you want to remove this airport?',
                    icon: 'warning',
                    showCancelButton: true,
                }).then((result) => {
                    if (result.value) {
                        Swal.fire('Deleting airport...');
                        Swal.showLoading();
                        $(this).closest('form').submit();
                    }
                });
            });
        </script>
    @endpush
    <p>
        <a href="{{ route('admin.airports.create') }}" class="btn btn-primary"><i class="fa fa-plus"></i> Add new
            Airport</a>&nbsp;
        <a href=" {{ route('admin.airportLinks.create') }}" class="btn btn-primary"><i class="fa fa-plus"></i> Add new
            Airport
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
                    @if($airport->flightsDep->isEmpty() && $airport->flightsArr->isEmpty() && $airport->eventDep->isEmpty() && $airport->eventArr->isEmpty())
                        <form action="{{ route('admin.airports.destroy', $airport) }}" method="post">
                            @method('DELETE')
                            <button class="btn btn-danger delete-airport"><i class="fa fa-trash"></i> Remove Airport
                            </button>
                            @csrf
                        </form>
                    @else
                        <button class="btn btn-danger disabled" disabled><i class="fa fa-trash"></i> Remove Airport
                        </button>
                    @endif
                </td>
            </tr>
        @empty
            @php
                flashMessage('warning', 'No airports found', 'No airports are in the system, consider adding one, using the button above');
            @endphp
            @include('layouts.alert')
        @endforelse
        {{ $airports->links() }}
    </table>
    {{ $airports->links() }}
@endsection

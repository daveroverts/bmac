@extends('layouts.app')

@section('content')
    <h3>Airports Overview</h3>
    <hr>
    @include('layouts.alert')
    @push('scripts')
        <script>
            $('.delete-airport').on('click', function(e) {
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
            $('.delete-unused-airports').on('click', function(e) {
                e.preventDefault();
                Swal.fire({
                    title: 'Are you sure',
                    text: 'Are you sure you want to delete all unused airports?',
                    icon: 'warning',
                    showCancelButton: true,
                }).then((result) => {
                    if (result.value) {
                        Swal.fire('Deleting unused airports...');
                        Swal.showLoading();
                        $(`#${$(this).attr('form')}`).submit();
                    }
                });
            });
        </script>
    @endpush
    <div class="d-flex flex-row flex-wrap">
        <a href="{{ route('admin.airports.create') }}" class="btn btn-primary m-1"><i class="fa fa-plus"></i> Add new
            Airport</a>
        <a href="{{ route('admin.airportLinks.create') }}" class="btn btn-primary m-1"><i class="fa fa-plus"></i> Add
            new
            Airport
            Link</a>
        <button class="btn btn-danger m-1 delete-unused-airports" form="delete-unused-airports"><i class="fa fa-trash"></i>
            Delete unused airports</button>
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
                    @if ($airport->flightsDep->isEmpty() && $airport->flightsArr->isEmpty() && $airport->eventDep->isEmpty() && $airport->eventArr->isEmpty())
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
        @endforeach
        {{ $airports->links() }}
    </table>
    {{ $airports->links() }}
    <x-form :action="route('admin.airports.destroyUnused')" id="delete-unused-airports" method="POST"
        style="display: none;"></x-form>
@endsection

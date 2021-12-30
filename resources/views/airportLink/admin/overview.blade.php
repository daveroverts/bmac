@extends('layouts.app')

@section('content')
    <h2>Airport Links Overview</h2>
    @include('layouts.alert')
    @push('scripts')
        <script>
            $('.delete-airportlink').on('click', function(e) {
                e.preventDefault();
                Swal.fire({
                    title: 'Are you sure',
                    text: 'Are you sure you want to remove this airport link?',
                    icon: 'warning',
                    showCancelButton: true,
                }).then((result) => {
                    if (result.value) {
                        Swal.fire('Deleting airport link...');
                        Swal.showLoading();
                        $(this).closest('form').submit();
                    }
                });
            });
        </script>
    @endpush
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
                        <button class="btn btn-danger delete-airportlink"><i class="fa fa-trash"></i> Remove Airport
                            Link
                        </button>
                        @csrf
                    </form>
                </td>
            </tr>
        @endforeach
        {{ $airportLinks->links() }}
    </table>
    {{ $airportLinks->links() }}
@endsection

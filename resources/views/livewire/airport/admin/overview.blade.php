<div>
    <div class="my-3 col-12 col-md-4">
        <div class="input-group">
            <span class="input-group-text"><i class="fa fa-search"></i></span>
            <input
                type="search"
                class="form-control"
                placeholder="{{ __('Search by ICAO, IATA or name...') }}"
                wire:model.live.debounce.300ms="search"
            >
        </div>
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
        @forelse ($airports as $airport)
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
                        $airport->flights_dep_count === 0 &&
                            $airport->flights_arr_count === 0 &&
                            $airport->event_dep_count === 0 &&
                            $airport->event_arr_count === 0)
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
        @empty
            <tr>
                <td colspan="5" class="text-center">{{ __('No airports found.') }}</td>
            </tr>
        @endforelse
    </table>
    {{ $airports->links() }}
</div>

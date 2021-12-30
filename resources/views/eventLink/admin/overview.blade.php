@extends('layouts.app')

@section('content')
    <h2>Event Links Overview</h2>
    @include('layouts.alert')
    @push('scripts')
        <script>
            $('.delete-eventlink').on('click', function(e) {
                e.preventDefault();
                Swal.fire({
                    title: 'Are you sure',
                    text: 'Are you sure you want to remove this event link?',
                    icon: 'warning',
                    showCancelButton: true,
                }).then((result) => {
                    if (result.value) {
                        Swal.fire('Deleting event link...');
                        Swal.showLoading();
                        $(this).closest('form').submit();
                    }
                });
            });
        </script>
    @endpush
    <p>
        <a href=" {{ route('admin.eventLinks.create') }}" class="btn btn-primary"><i class="fa fa-plus"></i> Add new
            Event
            Link</a>
    </p>
    <table class="table table-hover">
        <thead>
            <tr>
                <th scope="row">Event</th>
                <th scope="row">Type / Name</th>
                <th scope="row" colspan="2">Actions</th>
            </tr>
        </thead>
        @foreach ($eventLinks as $eventLink)
            <tr>
                <td>
                    <a href="{{ route('admin.events.show', $eventLink->event) }}">{{ $eventLink->event->name }}</a>
                </td>
                <td><a href="{{ $eventLink->url }}">{{ $eventLink->name ?? $eventLink->type->name }}</a></td>
                <td>
                    <a href="{{ route('admin.eventLinks.edit', $eventLink) }}">
                        <button class="btn btn-primary">
                            <i class="fa fa-edit"></i> Edit event Link
                        </button>
                    </a>
                </td>
                <td>
                    <form action="{{ route('admin.eventLinks.destroy', $eventLink) }}" method="post">
                        @method('DELETE')
                        <button class="btn btn-danger delete-eventlink"><i class="fa fa-trash"></i> Remove event
                            Link
                        </button>
                        @csrf
                    </form>
                </td>
            </tr>
        @endforeach
        {{ $eventLinks->links() }}
    </table>
    {{ $eventLinks->links() }}
@endsection

@extends('layouts.app')

@section('content')
    <h3>FAQ Overview</h3>
    <hr>
    @include('layouts.alert')
    <p>
        <a href="{{ route('admin.faq.create') }}" class="btn btn-primary"><i class="fa fa-plus"></i> {{ __('Add') }}
            new FAQ</a>
    </p>
    <table class="table table-hover">
        <thead>
            <tr>
                <th scope="row">ID</th>
                <th scope="row">Question</th>
                <th scope="row">Linked?</th>
                <th scope="row" colspan="2">Actions</th>
            </tr>
        </thead>
        @foreach ($faqs as $faq)
            <tr>
                <td>{{ $faq->id }}</td>
                <td>{{ $faq->question }}</td>
                <td>{{ $faq->events_count ? 'Yes' : 'No' }}</td>
                <td>
                    <a href="{{ route('admin.faq.edit', $faq) }}">
                        <button class="btn btn-primary">
                            <i class="fa fa-edit"></i> Edit FAQ
                        </button>
                    </a>
                </td>
                <td>
                    <form action="{{ route('admin.faq.destroy', $faq) }}" method="post">
                        @method('DELETE')
                        <x-confirm-button
                            confirm-text="Are you sure you want to remove this faq?"
                            loading-message="Deleting faq..."
                        ><i class="fa fa-trash"></i> Remove FAQ</x-confirm-button>
                        @csrf
                    </form>
                </td>
            </tr>
        @endforeach
        {{ $faqs->links() }}
    </table>
    {{ $faqs->links() }}
@endsection

@if(session('text'))
    @push('scripts')
        <script>
            swal({
                title: '{{ session('title') }}',
                text: '{{ session('text') }}',
                type: '{{ session('type') }}'
            })
        </script>
    @endpush
@endif

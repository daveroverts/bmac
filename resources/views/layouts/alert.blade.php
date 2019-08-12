@if(session('text'))
    @push('scripts')
        <script>
            Swal.fire({
                title: '{{ session('title') }}',
                text: '{{ session('text') }}',
                type: '{{ session('type') }}'
            })
        </script>
    @endpush
@endif

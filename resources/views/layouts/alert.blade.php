@if(session('text'))
    @push('scripts')
        <script>
            Swal.fire({
                title: '{{ session('title') }}',
                text: '{{ session('text') }}',
                icon: '{{ session('type') }}'
            })
        </script>
    @endpush
@endif

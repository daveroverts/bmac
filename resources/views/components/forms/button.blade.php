@props([
    'variant' => 'primary',
])

{{-- btn-primary --}}
{{-- btn-secondary --}}
{{-- btn-success --}}
{{-- btn-info --}}
{{-- btn-warning --}}
{{-- btn-danger --}}
{{-- btn-link --}}

<button {!! $attributes->merge([
    'class' => 'btn btn-' . $variant,
    'type' => 'submit',
]) !!}>
    {!! trim($slot) ?: __('Submit') !!}
</button>

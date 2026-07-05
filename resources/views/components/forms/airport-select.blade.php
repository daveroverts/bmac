@props(['name', 'label' => null, 'value' => null, 'placeholder' => null, 'help'])
@php
    $selectedId = old($name, $value);
    $selectedAirport = $selectedId ? \App\Models\Airport::find($selectedId) : null;
@endphp
<x-forms.form-group :name="$name" :label="$label">
    <select name="{{ $name }}" data-airport-select data-search-url="{{ route('admin.airports.search') }}"
        {{ $attributes->class(['form-select', 'is-invalid' => $errors->has($name)]) }}>
        @if ($placeholder && !$selectedAirport)
            <option value="">{{ $placeholder }}</option>
        @endif

        @if ($selectedAirport)
            <option value="{{ $selectedAirport->id }}" selected>{{ $selectedAirport->dropdownLabel() }}</option>
        @endif
    </select>

    @isset($help)
        <small id="{{ $name }}-help" class="form-text">
            {!! $help !!}
        </small>
    @endisset
</x-forms.form-group>

@props(['name', 'label', 'value', 'inline' => false, 'shouldBeChecked' => false])

@php
    $id = $name . '-' . $value;
@endphp

<div @class([
    'custom-control custom-radio',
    'custom-control-inline' => $inline,
])>
    <x-input type="radio" :name="$name" :value="$value" :id="$id"
        {{ $attributes->class(['custom-control-input', 'is-invalid' => $errors->has($name)])->merge(['checked' => $shouldBeChecked]) }}
    />

    <x-forms.label :for="$id" :label="$label" class="custom-control-label" />
</div>

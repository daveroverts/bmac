@props(['name', 'label', 'value', 'inline' => false])

@php
    $id = $name . '-' . $value;
@endphp

<div @class([
    'custom-control custom-radio',
    'custom-control-inline' => $inline,
])>
    <x-input type="radio" :name="$name" :value="$value" :id="$id"
        {{ $attributes->class(['custom-control-input', 'is-invalid' => $errors->has($name)])->merge(['checked' => '']) }} />

    <x-forms.label :for="$id" :label="$label" class="custom-control-label" />
</div>

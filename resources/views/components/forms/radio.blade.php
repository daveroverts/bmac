@props(['name', 'label', 'value', 'inline' => false, 'shouldBeChecked' => false])

@php
    $id = $name . '-' . $value;
@endphp

<div @class([
    'custom-control custom-radio',
    'custom-control-inline' => $inline,
])>
    <input
        type="radio"
        name="{{ $name }}"
        id="{{ $id }}"
        value="{{ $value }}"
        @if($shouldBeChecked) checked @endif
        {{ $attributes->class(['custom-control-input', 'is-invalid' => $errors->has($name)]) }}
    >

    <x-forms.label :for="$id" :label="$label" class="custom-control-label"/>
</div>

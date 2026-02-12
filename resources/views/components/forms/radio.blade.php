@props(['name', 'label', 'value', 'inline' => false, 'shouldBeChecked' => false])

@php
    $id = $name . '-' . $value;
@endphp

<div @class([
    'form-check',
    'form-check-inline' => $inline,
])>
    <input
        type="radio"
        name="{{ $name }}"
        id="{{ $id }}"
        value="{{ $value }}"
        @if($shouldBeChecked) checked @endif
        {{ $attributes->class(['form-check-input', 'is-invalid' => $errors->has($name)]) }}
    >

    <x-forms.label :for="$id" :label="$label" class="form-check-label"/>
</div>

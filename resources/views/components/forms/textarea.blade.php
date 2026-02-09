@props(['name', 'label' => null, 'value' => null, 'help', 'tinymce' => false])

<x-forms.form-group :name="$name" :label="$label">
    <textarea name="{{ $name }}" id="{{ $name }}" @class([
        'form-control',
        'tinymce' => $tinymce,
        'is-invalid' => $errors->has($name),
    ])>{{ old($name, $value) }}</textarea>

    <x-error :field="$name" class="invalid-feedback" />

    @isset($help)
        <small id="{{ $name }}-help" class="form-text text-muted">
            {!! $help !!}
        </small>
    @endisset
</x-forms.form-group>

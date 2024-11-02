@props(['name', 'label' => null, 'value' => null, 'options', 'placeholder' => null, 'help'])
<x-forms.form-group :name="$name" :label="$label">
    <select name="{{ $name }}" {{ $attributes->class(['custom-select', 'is-invalid' => $errors->has($name)]) }}>
        @if ($placeholder)
            <option value="" disabled selected>{{ $placeholder }}</option>
        @endif

        @foreach ($options as $key => $option)
            <option value="{{ $key }}" @selected(old($name, $value) == $key)>{{ $option }}</option>
        @endforeach
    </select>

    @isset($help)
        <small id="{{ $name }}-help" class="form-text text-muted">
            {!! $help !!}
        </small>
    @endisset

</x-forms.form-group>

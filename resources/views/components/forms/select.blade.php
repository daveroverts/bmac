@props(['name', 'label' => null, 'value' => null, 'options'])
<x-forms.form-group :name="$name" :label="$label">
    <select name="{{ $name }}" {{ $attributes->class(['custom-select', 'is-invalid' => $errors->has($name)]) }}>


        @foreach ($options as $key => $option)
            <option value="{{ $key }}" @selected(old($name, $value) == $key)>{{ $option }}</option>
        @endforeach
    </select>

</x-forms.form-group>

@props(['name', 'label', 'value' => null])

<div class="form-check">
    <x-checkbox :name="$name" :value="$value"
        {{ $attributes->class(['form-check-input', 'is-invalid' => $errors->has($name)]) }} />

    <x-forms.label :for="$name" :label="$label" class="form-check-label" />
</div>

@props(['name', 'label', 'value' => null])

<div class="custom-control custom-checkbox">
    <x-checkbox :name="$name" :value="$value"
        {{ $attributes->class(['custom-control-input', 'is-invalid' => $errors->has($name)]) }} />

    <x-forms.label :for="$name" :label="$label" class="custom-control-label" />
</div>

@props([
    'name',
    'type' => 'text',
    'label' => null,
    'value' => null,
    'prepend',
    'append',
    'help',
    'inputGroupClass' => '',
])

<x-forms.form-group :name="$name" :label="$label">
    <div {{ $attributes->class(['input-group has-validation', $inputGroupClass]) }}>
        @isset($prepend)
            <span class="input-group-text">
                {!! $prepend !!}
            </span>
        @endisset
        <x-input :name="$name" :value="$value" :type="$type"
            {{ $attributes->class(['form-control', 'is-invalid' => $errors->has($name)]) }} />

        <x-error :field="$name" class="invalid-feedback" />

        @isset($append)
            <span class="input-group-text">
                {!! $append !!}
            </span>
        @endisset

    </div>
    @isset($help)
        <small id="{{ $name }}-help" class="form-text">
            {!! $help !!}
        </small>
    @endisset
</x-forms.form-group>

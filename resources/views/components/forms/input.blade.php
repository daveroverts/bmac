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
            <div class="input-group-prepend">
                <div class="input-group-text">
                    {!! $prepend !!}
                </div>
            </div>
        @endisset
        <x-input :name="$name" :value="$value" :type="$type"
            {{ $attributes->class(['form-control', 'is-invalid' => $errors->has($name)]) }} />

        <x-error :field="$name" class="invalid-feedback" />

        @isset($append)
            <div class="input-group-append">
                <div class="input-group-text">
                    {!! $append !!}
                </div>
            </div>
        @endisset

    </div>
    @isset($help)
        <small id="{{ $name }}-help" class="form-text text-muted">
            {!! $help !!}
        </small>
    @endisset
</x-forms.form-group>

@props(['name', 'label' => null, 'prepend', 'append'])

<div class="form-group">
    <x-forms.label :for="$name" :label="$label" />

    <div class="input-group has-validation">
        @isset($prepend)
            <div class="input-group-prepend">
                <div class="input-group-text">
                    {!! $prepend !!}
                </div>
            </div>
        @endisset
        <x-input :name="$name" {{ $attributes->class(['form-control', 'is-invalid' => $errors->has($name)]) }} />


        <x-error :field="$name" class="invalid-feedback" />

        @isset($append)
            <div class="input-group-append">
                <div class="input-group-text">
                    {!! $append !!}
                </div>
            </div>
        @endisset
    </div>
</div>

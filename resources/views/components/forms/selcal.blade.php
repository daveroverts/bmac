@props([
    'label' => null,
    'value1' => null,
    'value2' => null,
    'help' => null,
])

<x-forms.form-group :label="$label" name="selcal1">
    <div @class([
        'input-group',
        'has-validation' => $errors->has('selcal1') || $errors->has('selcal2')
    ])>
        <x-forms.inputs.input
            name="selcal1"
            minlength="2"
            maxlength="2"
            :value="$value1"
            @class([
                'form-control',
                'is-invalid' => $errors->has('selcal1')
            ])
        />

        <span class="input-group-text">-</span>

        <x-forms.inputs.input
            name="selcal2"
            minlength="2"
            maxlength="2"
            :value="$value2"
            @class([
                'form-control',
                'is-invalid' => $errors->has('selcal2')
            ])
        />

        <x-forms.error :field="'selcal1'" class="invalid-feedback" />
    </div>

    @isset($help)
        <small id="selcal-help" class="form-text">
            {!! $help !!}
        </small>
    @endisset
</x-forms.form-group>

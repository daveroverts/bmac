<div class="custom-control custom-radio">
    <input {!! $attributes->merge(['class' => 'custom-control-input ' . ($hasError($name) ? 'is-invalid' : '')]) !!}
        type="radio"

        @if($isWired())
            wire:model{!! $wireModifier() !!}="{{ $name }}"
        @endif

        name="{{ $name }}"
        value="{{ $value }}"

        @if($checked)
            checked="checked"
        @endif

        @if($label && !$attributes->get('id'))
            id="{{ $id() }}"
        @endif
    />

   <x-form-label :label="$label" :for="$attributes->get('id') ?: $id()" class="custom-control-label" />

    {!! $help ?? null !!}

    @if($hasErrorAndShow($name))
        <x-form-errors :name="$name" />
    @endif
</div>
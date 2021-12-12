<div class="custom-control custom-checkbox">
    <input {!! $attributes->merge(['class' => 'custom-control-input ' . ($hasError($name) ? 'is-invalid' : '')]) !!}
        type="checkbox"
        value="{{ $value }}"

        @if($isWired())
            wire:model{!! $wireModifier() !!}="{{ $name }}"
        @endif

        name="{{ $name }}"

        @if($label && !$attributes->get('id'))
            id="{{ $id() }}"
        @endif

        @if($checked)
            checked="checked"
        @endif
    />

    <x-form-label :label="$label" :for="$attributes->get('id') ?: $id()" class="custom-control-label" />

    {!! $help ?? null !!}

    @if($hasErrorAndShow($name))
        <x-form-errors :name="$name" />
    @endif
</div>
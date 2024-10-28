@props(['name', 'label' => null, 'inline' => false, 'help'])

<div class="form-group">
    <x-forms.label :for="$name" :label="$label" />

    <div {{ $attributes->class(['form-inline' => $inline]) }}>
        {!! $slot !!}
    </div>

    @isset($help)
        <small id="{{ $name }}-help" class="form-text text-muted">
            {!! $help !!}
        </small>
    @endisset
</div>

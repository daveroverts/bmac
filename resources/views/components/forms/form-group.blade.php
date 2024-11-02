@props(['name' => null, 'label' => null, 'inline' => false, 'help'])

<div class="form-group">
    @if ($name || $label)
        <x-forms.label :for="$name" :label="$label" />
    @endif

    <div {{ $attributes->class(['d-flex flex-row flex-wrap inline-space' => $inline]) }}>
        {!! $slot !!}
    </div>

    @isset($help)
        <small id="{{ $name }}-help" class="form-text text-muted">
            {!! $help !!}
        </small>
    @endisset
</div>

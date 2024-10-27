@props(['name', 'label' => null, 'inline' => false])

<div class="form-group">
    <x-forms.label :for="$name" :label="$label" />

    <div>
        {!! $slot !!}
    </div>
</div>

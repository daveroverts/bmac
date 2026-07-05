<div wire:ignore>
    <input
        x-data="{
            picker: null,
        }"
        x-init="$nextTick(() => {
            if (picker) return;

            picker = flatpickr($root, {{ $jsonOptions() }});
            window.flatpickrs = window.flatpickrs || {};
            window.flatpickrs['{{ $name }}'] = picker;
            document.dispatchEvent(new CustomEvent('flatpickr:ready', { detail: { name: '{{ $name }}' } }));
        })"
        name="{{ $name }}"
        type="text"
        id="{{ $id }}"
        placeholder="{{ $placeholder }}"
        @if($value) value="{{ $value }}" @endif
        {{ $attributes }}
    />
</div>

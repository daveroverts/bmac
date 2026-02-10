@props([
    'confirmTitle' => 'Are you sure',
    'confirmText',
    'loadingMessage',
    'variant' => 'danger',
])

<button
    x-data="{ confirmTitle: @js($confirmTitle), confirmText: @js($confirmText), loadingMessage: @js($loadingMessage) }"
    x-on:click.prevent="
        Swal.fire({
            title: confirmTitle,
            text: confirmText,
            icon: 'warning',
            showCancelButton: true,
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire(loadingMessage);
                Swal.showLoading();
                const formId = $el.getAttribute('form');
                (formId ? document.getElementById(formId) : $el.closest('form')).submit();
            }
        })
    "
    {!! $attributes->merge([
        'class' => 'btn btn-' . $variant,
    ]) !!}
>
    {!! $slot !!}
</button>

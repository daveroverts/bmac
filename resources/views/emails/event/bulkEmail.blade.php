@component('mail::message')
# {{ $subject }}

Dear **{{ $full_name }}**,

{!! $content !!}

@lang('Regards'),

**{{ config('mail.from.name', config('app.name')) }}**
@endcomponent

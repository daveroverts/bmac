@component('mail::message')
# {{ $subject }}

Dear **{{ $full_name }}**,

{!! $content !!}

@lang('Regards'),

**{{ config('mail.from.name', config('app.name')) }}**
This mailbox is not being monitored. Please do not reply to this email. If you have any queries please direct them to the Cross The Land staff members via Discord.
@endcomponent

@component('mail::message')
# {{ $subject }}

Dear **{{ $user->full_name }}**,

{!! $content !!}

Regards,

**Dutch VACC**
@endcomponent
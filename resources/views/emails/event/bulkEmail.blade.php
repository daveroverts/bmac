@component('mail::message')
    <h1>{{ $subject }}</h1>

    <p>Dear <strong>{{ $user->full_name }}</strong></p>

    {{ $message }}

    <p>Regards,</p>

    <p><strong>Dutch VACC</strong></p>
@endcomponent
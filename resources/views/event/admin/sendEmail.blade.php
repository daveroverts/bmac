@extends('layouts.app')

@section('content')
    <x-forms.alert />
    @include('layouts.alert')
    @push('scripts')
        <script type="module">
            $('.send-final-email').on('click', function(e) {
                e.preventDefault();
                if ($('#testmode1').prop('checked')) {
                    Swal.fire('Sending test Email...');
                    Swal.showLoading();
                    var url = '{{ route('admin.events.email.final', $event) }}';
                    axios.post(url, {
                            'testmode': 1,
                            '_method': 'PATCH',
                        })
                        .then(function(response) {
                            Swal.fire(response.data.success);
                        });
                } else {
                    Swal.fire({
                        title: 'Are you sure',
                        text: 'Are you sure you want to send the Final Information Email?',
                        icon: 'warning',
                        showCancelButton: true,
                    }).then((result) => {
                        if (result.value) {
                            Swal.fire('Sending Final Information Email...');
                            Swal.showLoading();
                            $(this).closest('form').submit();
                        }
                    });
                }
            });

            $('.send-email').on('click', function(e) {
                e.preventDefault();
                if ($('#testmode2').prop('checked')) {
                    Swal.fire('Sending test Email...');
                    Swal.showLoading();
                    var url = '{{ route('admin.events.email', $event) }}';
                    axios.post(url, {
                            'subject': $('#subject').val(),
                            'message': tinymce.activeEditor.getContent(),
                            'testmode': 1,
                            '_method': 'PATCH',
                        })
                        .then(function(response) {
                            console.log(response);
                            Swal.fire(response.data.success);
                        });
                } else {
                    Swal.fire({
                        title: 'Are you sure',
                        text: 'Are you sure you want to send a Email?',
                        icon: 'warning',
                        showCancelButton: true,
                    }).then((result) => {
                        if (result.value) {
                            Swal.fire('Sending Email...');
                            Swal.showLoading();
                            $(this).closest('form').submit();
                        }
                    });
                }

            });
        </script>
    @endpush
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ $event->name }} | {{ __('Send Bulk E-mail') }}</div>

                <div class="card-body">

                    <x-form :action="route('admin.events.email.final', $event)" method="PATCH">
                        <x-forms.form-group :help="__('Send a random Final Information E-mail to yourself')">
                            <x-forms.checkbox name="testmode1" :label="__('Test mode')" />
                        </x-forms.form-group>

                        <x-forms.form-group :help="__('Send to all particpants, even though they already received it (and no edit was made)')">
                            <x-forms.checkbox name="forceSend" :label="__('Send to everybody')" />
                        </x-forms.form-group>
                        <x-forms.button class="send-final-email" type="submit">
                            <i class="fa fa-envelope"></i> {!! __('Send <strong>Final Information</strong> E-mail') !!}
                        </x-forms.button>
                    </x-form>

                    <hr>

                    <x-form :action="route('admin.events.email', $event)" method="PATCH">
                        <x-forms.input name="subject" :label="__('Subject')" required />
                        <x-forms.textarea name="message" :label="__('Message')" tinymce :help="__('Salutation and closing are already included')" />

                        <x-forms.form-group :help="__('Send a E-mail to yourself')">
                            <x-forms.checkbox name="testmode2" :label="__('Test mode')" />
                        </x-forms.form-group>

                        <x-forms.button class="send-email" type="submit">
                            <i class="fa fa-envelope"></i> {{ __('Send E-mail') }}
                        </x-forms.button>
                    </x-form>

                </div>
            </div>
        </div>
    </div>
@endsection

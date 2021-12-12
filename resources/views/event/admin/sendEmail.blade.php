@extends('layouts.app')

@section('content')
    <x-forms.alert />
    @include('layouts.alert')
    @push('scripts')
        <script>
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
                        <x-form-group inline>
                            <x-form-checkbox name="testmode1" id="testmode1" :label="__('Test mode')">
                                @slot('help')
                                    <small class="form-text text-muted">
                                        {{ __('Send a random Final Information E-mail to yourself') }}
                                    </small>
                                @endslot
                            </x-form-checkbox>
                            <x-form-checkbox name="forceSend" :label="__('Send to everybody')">
                                @slot('help')
                                    <small class="form-text text-muted">
                                        {{ __('Send to all particpants, even though they already received it (and no edit was made)') }}
                                    </small>
                                @endslot
                            </x-form-checkbox>
                        </x-form-group>
                        <x-form-submit class="send-final-email">
                            <i class="fa fa-envelope"></i> {!! __('Send <strong>Final Information</strong> E-mail') !!}
                        </x-form-submit>
                    </x-form>

                    <hr>

                    <x-form :action="route('admin.events.email', $event)" method="PATCH">
                        <x-form-input name="subject" id="subject" :label="__('Subject')" required />
                        <x-form-textarea name="message" :label="__('Message')" class="tinymce">
                            @slot('help')
                                <small class="form-text text-muted">
                                    {{ __('Salutation and closing are already included') }}
                                </small>
                            @endslot
                        </x-form-textarea>

                        <x-form-group>
                            <x-form-checkbox name="testmode" id="testmode2" :label="__('Test mode')">
                                @slot('help')
                                    <small class="form-text text-muted">
                                        {{ __('Send a E-mail to yourself') }}
                                    </small>
                                @endslot
                            </x-form-checkbox>
                        </x-form-group>

                        <x-form-submit class="send-email">
                            <i class="fa fa-envelope"></i> {{ __('Send E-mail') }}
                        </x-form-submit>
                    </x-form>

                </div>
            </div>
        </div>
    </div>
@endsection

@extends('layouts.app')

@section('content')
    @if (count($errors) > 0)
        <div class="alert alert-dismissible alert-danger">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    @include('layouts.alert')
    @push('scripts')
        <script>
            $('.send-final-email').on('click', function (e) {
                e.preventDefault();
                if ($('#testmode1').prop('checked')) {
                    Swal.fire('Sending test Email...');
                    Swal.showLoading();
                    var url = '{{ route('admin.events.email.final', $event) }}';
                    axios.post(url, {
                        'testmode': 1,
                        '_method': 'PATCH',
                    })
                        .then(function (response) {
                            Swal.fire(response.data.success);
                        });
                } else {
                    Swal.fire({
                        title: 'Are you sure',
                        text: 'Are you sure you want to send the Final Information Email?',
                        type: 'warning',
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

            $('.send-email').on('click', function (e) {
                e.preventDefault();
                if ($('#testmode2').prop('checked')) {
                    Swal.fire('Sending test Email...');
                    Swal.showLoading();
                    var url = '{{ route('admin.events.email', $event) }}';
                    axios.post(url, {
                        'subject': $('#subject').val(),
                        'message': tinymce.get('description').getContent(),
                        'testmode': 1,
                        '_method': 'PATCH',
                    })
                        .then(function (response) {
                            console.log(response);
                            Swal.fire(response.data.success);
                        });
                } else {
                    Swal.fire({
                        title: 'Are you sure',
                        text: 'Are you sure you want to send a Email?',
                        type: 'warning',
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
                <div class="card-header">{{ $event->name }} | Send Bulk E-mail</div>

                <div class="card-body">

                    <form method="POST" action="{{ route('admin.events.email.final', $event) }}">
                        @csrf
                        @method('PATCH')
                        {{--Send Final Information E-mail--}}
                        <div class="form-group row">
                            <label class="col-md-4 col-form-label text-md-right"></label>

                            <div class="col-md-6">
                                <button class="btn btn-primary send-final-email">
                                    <i class="fa fa-envelope"></i> Send <strong>Final Information</strong> E-mail
                                </button>
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="testmode1">
                                    <label class="custom-control-label" for="testmode1">Test mode</label>
                                </div>
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="force-send" name="forceSend">
                                    <label class="custom-control-label" for="force-send"><abbr
                                            title="Send to all particpants, even though they already received it (and no edit was made)">Send to everybody</abbr></label>
                                </div>
                            </div>
                        </div>
                    </form>

                    <hr>

                    <form method="POST" action="{{ route('admin.events.email', $event) }}">
                        @csrf
                        @method('PATCH')

                        {{--Subject--}}
                        <div class="form-group row">
                            <label for="subject" class="col-md-4 col-form-label text-md-right"> Subject</label>

                            <div class="col-md-6">
                                <input id="subject" type="text"
                                       class="form-control{{ $errors->has('subject') ? ' is-invalid' : '' }}"
                                       name="subject"
                                       value="{{ old('subject') }}" required autofocus>

                                @if ($errors->has('subject'))
                                    <span class="invalid-feedback">
                                        <strong>{{ $errors->first('subject') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        {{-- Description --}}
                        <div class="form-group row">
                            <textarea id="description" name="message"
                                      rows="10">{!! old(html_entity_decode('message')) !!}</textarea>
                        </div>

                        {{--Send--}}
                        <div class="form-group row mb-0">
                            <div class="col-md-6 offset-md-4">
                                <button type="submit" class="btn btn-primary send-email">
                                    <i class="fas fa-envelope"></i> Send E-mail
                                </button>
                            </div>
                            <div class="col-md-6 offset-md-4">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="testmode2">
                                    <label class="custom-control-label" for="testmode2">Test mode</label>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

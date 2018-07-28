@extends('layouts.app')

@section('content')
    <script src="{{ asset('js/tinymce/tinymce.min.js') }}"></script>
    <script>tinymce.init({
            selector: 'textarea',
            plugins: 'code'
        });</script>
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
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ $event->name }} | Send Bulk E-mail</div>

                <div class="card-body">
                    <form method="POST" action="{{ route('event.email',$event->id) }}">
                        @csrf
                        @method('PATCH')

                        {{--Send Final Information E-mail--}}
                        <div class="form-group row">
                            <label class="col-md-4 col-form-label text-md-right"></label>

                            <div class="col-md-6">
                                <a href="{{ route('event.email.final',$event->id) }}" class="btn btn-primary"><i class="fa fa-envelope"></i> Send Final Information E-mail</a>
                            </div>
                        </div>
                        {{--Subject--}}
                        <div class="form-group row">
                            <label for="subject" class="col-md-4 col-form-label text-md-right"> Subject</label>

                            <div class="col-md-6">
                                <input id="subject" type="text"
                                       class="form-control{{ $errors->has('subject') ? ' is-invalid' : '' }}" name="subject"
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
                            <textarea id="message" name="message"
                                      rows="10">{!! old(html_entity_decode('message')) !!}</textarea>
                        </div>

                        {{--Send--}}
                        <div class="form-group row mb-0">
                            <div class="col-md-6 offset-md-4">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-envelope"></i> Send E-mail
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

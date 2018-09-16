@extends('layouts.app')

@section('content')
    <script src="{{ asset('js/tinymce/tinymce.min.js') }}"></script>
    <script>tinymce.init({
            selector: 'textarea',
            plugins: 'code link',
            menubar: 'insert'
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
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Add new Event</div>

                <div class="card-body">
                    <form method="POST" action="{{ route('event.store') }}">
                        @csrf
                        {{--Name--}}
                        <div class="form-group row">
                            <label for="name" class="col-md-4 col-form-label text-md-right"> Name</label>

                            <div class="col-md-6">
                                <input id="name" type="text"
                                       class="form-control{{ $errors->has('name') ? ' is-invalid' : '' }}" name="name"
                                       value="{{ old('name') }}" required autofocus>

                                @if ($errors->has('name'))
                                    <span class="invalid-feedback">
                                        <strong>{{ $errors->first('name') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        {{--Type--}}
                        <div class="form-group row">
                            <label for="eventType" class="col-md-4 col-form-label text-md-right"> Type</label>

                            <div class="col-md-6">
                                <select class="custom-select form-control{{ $errors->has('eventType') ? ' is-invalid' : '' }}" name="eventType">
                                    <option value="">Choose an event type...</option>
                                    @foreach($eventTypes as $eventType)
                                        <option value="{{ $eventType->id }}" {{ old('eventType') == $eventType->id ? 'selected' : '' }}>{{ $eventType->name }}</option>
                                    @endforeach
                                </select>

                                @if ($errors->has('eventType'))
                                    <span class="invalid-feedback">
                                        <strong>{{ $errors->first('eventType') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        {{--Airport--}}
                        <div class="form-group row">
                            <label for="end" class="col-md-4 col-form-label text-md-right"> Airport</label>

                            <div class="col-md-6">
                                <select class="custom-select form-control{{ $errors->has('from') ? ' is-invalid' : '' }}" name="airport">
                                    <option value="">Choose an airport...</option>
                                    @foreach($airports as $airport)
                                        <option value="{{ $airport->icao }}" {{ old('from') == $airport->icao ? 'selected' : '' }}>{{ $airport->icao }} [{{ $airport->name }} ({{ $airport->iata }})]</option>
                                    @endforeach
                                </select>

                                @if ($errors->has('airport'))
                                    <span class="invalid-feedback">
                                        <strong>{{ $errors->first('airport') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        {{--Date Event--}}
                        <div class="form-group row">
                            <label for="dateEvent" class="col-md-4 col-form-label text-md-right"><i
                                        class="fa fa-calendar"></i> Date Event</label>

                            <div class="col-md-6">
                                <input type="text"
                                       class="form-control{{ $errors->has('date') ? ' is-invalid' : '' }} datepicker"
                                       name="dateEvent" value="{{ old('dateEvent') }}" required>

                                @if ($errors->has('dateEvent'))
                                    <span class="invalid-feedback">
                                        <strong>{{ $errors->first('dateEvent') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        {{--Time begin--}}
                        <div class="form-group row">
                            <label for="timeBeginEvent" class="col-md-4 col-form-label text-md-right"><i
                                        class="far fa-clock"></i> Begin (UTC)</label>

                            <div class="col-md-6">
                                <input id="timeBeginEvent" type="time"
                                       class="form-control{{ $errors->has('timeEndEvent') ? ' is-invalid' : '' }}"
                                       name="timeBeginEvent" value="{{ old('timeBeginEvent') }}" required>

                                @if ($errors->has('name'))
                                    <span class="invalid-feedback">
                                        <strong>{{ $errors->first('timeBeginEvent') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        {{--Time end--}}
                        <div class="form-group row">
                            <label for="timeEndEvent" class="col-md-4 col-form-label text-md-right"><i
                                        class="far fa-clock fa-flip-horizontal"></i> End (UTC)</label>

                            <div class="col-md-6">
                                <input id="timeEndEvent" type="time"
                                       class="form-control{{ $errors->has('timeEndEvent') ? ' is-invalid' : '' }}"
                                       name="timeEndEvent" value="{{ old('timeEndEvent') }}" required>

                                @if ($errors->has('timeEndEvent'))
                                    <span class="invalid-feedback">
                                        <strong>{{ $errors->first('timeEndEvent') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        {{--Date--}}
                        <div class="form-group row">
                            <label for="dateBeginBooking" class="col-md-4 col-form-label text-md-right"><i
                                        class="fa fa-calendar"></i> Date Start Bookings</label>

                            <div class="col-md-6">
                                <input type="text"
                                       class="form-control{{ $errors->has('date') ? ' is-invalid' : '' }} datepicker"
                                       name="dateBeginBooking" value="{{ old('dateBeginBooking') }}" required>

                                @if ($errors->has('dateBeginBooking'))
                                    <span class="invalid-feedback">
                                        <strong>{{ $errors->first('dateBeginBooking') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        {{--Time begin--}}
                        <div class="form-group row">
                            <label for="timeBeginBooking" class="col-md-4 col-form-label text-md-right"><i
                                        class="far fa-clock"></i> Begin (UTC)</label>

                            <div class="col-md-6">
                                <input id="timeBeginEvent" type="time"
                                       class="form-control{{ $errors->has('timeBeginBooking') ? ' is-invalid' : '' }}"
                                       name="timeBeginBooking" value="{{ old('timeBeginBooking') }}" required>

                                @if ($errors->has('name'))
                                    <span class="invalid-feedback">
                                        <strong>{{ $errors->first('timeBeginBooking') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        {{--Date--}}
                        <div class="form-group row">
                            <label for="dateEndBooking" class="col-md-4 col-form-label text-md-right"><i
                                        class="fa fa-calendar"></i> Date End Bookings</label>

                            <div class="col-md-6">
                                <input type="text"
                                       class="form-control{{ $errors->has('date') ? ' is-invalid' : '' }} datepicker"
                                       name="dateEndBooking" value="{{ old('dateEndBooking') }}" required>

                                @if ($errors->has('dateBeginBooking'))
                                    <span class="invalid-feedback">
                                        <strong>{{ $errors->first('dateEndBooking') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        {{--Time end--}}
                        <div class="form-group row">
                            <label for="timeEndBooking" class="col-md-4 col-form-label text-md-right"><i
                                        class="far fa-clock fa-flip-horizontal"></i> End (UTC)</label>

                            <div class="col-md-6">
                                <input id="timeEndBooking" type="time"
                                       class="form-control{{ $errors->has('timeEndBooking') ? ' is-invalid' : '' }}"
                                       name="timeEndBooking" value="{{ old('timeEndBooking') }}" required>

                                @if ($errors->has('timeEndBooking'))
                                    <span class="invalid-feedback">
                                        <strong>{{ $errors->first('timeEndBooking') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        {{--Feedback Form--}}
                        <div class="form-group row">
                            <label for="timeFeedbackForm" class="col-md-4 col-form-label text-md-right"><i
                                        class="far fa-clock fa-flip-horizontal"></i> Send Feedback Form (hours after
                                end)</label>

                            <div class="col-md-6">
                                <input id="timeFeedbackForm" type="time"
                                       class="form-control{{ $errors->has('timeFeedbackForm') ? ' is-invalid' : '' }}"
                                       name="timeFeedbackForm" value="{{ old('timeFeedbackForm') }}" required>

                                @if ($errors->has('timeFeedbackForm'))
                                    <span class="invalid-feedback">
                                        <strong>{{ $errors->first('timeFeedbackForm') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        {{-- Description --}}
                        <div class="form-group row">
                            <textarea id="description" name="description"
                                      rows="10">{!! old(html_entity_decode('description')) !!}</textarea>
                        </div>

                        {{--Add--}}
                        <div class="form-group row mb-0">
                            <div class="col-md-6 offset-md-4">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fa fa-plus"></i> Add
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@extends('layouts.app')

@section('content')
    @if (count($errors) > 0)
        <div class="alert alert-danger">
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
            $('.unlik-event').on('click', function (e) {
                e.preventDefault();
                swal({
                    title: 'Are you sure',
                    text: 'Are you sure you want to unlink this event?',
                    type: 'warning',
                    showCancelButton: true,
                }).then((result) => {
                    if (result.value) {
                        swal('Unlinking event...');
                        swal.showLoading();
                        $(this).closest('form').submit();
                    }
                });
            });
        </script>
    @endpush
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ $faq->id ? 'Edit' : 'Add new' }} FAQ</div>

                <div class="card-body">
                    <form method="POST" action="{{ $faq->id ? route('faq.update', $faq) : route('faq.store') }}">
                        @csrf
                        @if($faq->id)
                            @method('PATCH')
                        @endif

                        {{--Online--}}
                        <div class="form-group row">
                            <label for="is_online" class="col-md-4 col-form-label text-md-right"> Online?</label>

                            <div class="col-md-6">
                                <div class="custom-control custom-control-inline custom-radio">
                                    <input type="radio" value="0" id="is_online0" name="is_online"
                                           class="custom-control-input" {{ old('is_online', $faq->is_online === 0) == 0 ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="is_online0">No</label>
                                </div>
                                <div class="custom-control custom-control-inline custom-radio">
                                    <input type="radio" value="1" id="is_online1" name="is_online"
                                           class="custom-control-input" {{ old('is_online', $faq->is_online === 1) == 1 ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="is_online1">Yes</label>
                                </div>

                                @if ($errors->has('is_online'))
                                    <span class="invalid-feedback">
                                        <strong>{{ $errors->first('is_online') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        {{--Question--}}
                        <div class="form-group row">
                            <label for="question" class="col-md-4 col-form-label text-md-right"> Question</label>

                            <div class="col-md-6">
                                <input id="question" type="text"
                                       class="form-control{{ $errors->has('question') ? ' is-invalid' : '' }}"
                                       name="question"
                                       value="{{ old('question', $faq->question) }}" required>

                                @if ($errors->has('question'))
                                    <span class="invalid-feedback">
                                        <strong>{{ $errors->first('question') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        {{--Answer--}}
                        <div class="form-group row">
                            <label for="answer" class="col-md-4 col-form-label text-md-right"> Answer</label>

                            <textarea id="description" name="answer"
                                      rows="10">{!! html_entity_decode(old('answer', $faq->answer)) !!}</textarea>

                            @if ($errors->has('answer'))
                                <span class="invalid-feedback">
                                        <strong>{{ $errors->first('answer') }}</strong>
                                    </span>
                            @endif
                        </div>

                        {{--Add/Edit--}}
                        <div class="form-group row mb-0">
                            <div class="col-md-6 offset-md-4">
                                <button type="submit" class="btn btn-primary">
                                    @if($faq->id)
                                        <i class="fa fa-check"></i> Edit
                                    @else
                                        <i class="fa fa-plus"></i> Add
                                    @endif
                                </button>
                            </div>
                        </div>
                    </form>
                </div>

                @if($faq->id)
                    <div class="card-header">Related events</div>
                    @if($events)
                        <div class="card-body">
                            <table class="table table-hover">
                                <thead>
                                <tr>
                                    <th scope="row">Name</th>
                                    <th scope="row">Actions</th>
                                </tr>
                                </thead>
                                @foreach($events as $event)
                                    <tr>
                                        <td>{{ $event->name }} [{{ $event->startEvent->format('d-m-Y') }}]</td>
                                        <td>
                                            <form
                                                action="{{ route('faq.toggleEvent', ['faq' => $faq, 'event' => $event->id]) }}"
                                                method="post">
                                                @csrf
                                                @method('PATCH')

                                                @if($faq->events()->where('event_id', $event->id)->first())
                                                    <button class="btn btn-danger unlink-event">Unlink event</button>
                                                @else
                                                    <button class="btn btn-success">Link event</button>
                                                @endif
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </table>
                        </div>
                    @endif
                @endif
            </div>
        </div>
    </div>
@endsection

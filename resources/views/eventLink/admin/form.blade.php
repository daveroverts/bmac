@extends('layouts.app')

@section('content')
    <x-forms.alert />
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ $eventLink->id ? 'Edit' : 'Add new' }} Event Link</div>

                <div class="card-body">
                    <form method="POST"
                        action="{{ $eventLink->id ? route('admin.eventLinks.update', $eventLink) : route('admin.eventLinks.store') }}">
                        @csrf
                        @if ($eventLink->id)
                            @method('PATCH')
                        @endif
                        {{-- Type --}}
                        <div class="form-group row">
                            <label for="event_link_type_id" class="col-md-4 col-form-label text-md-right">Type</label>

                            <div class="col-md-6">
                                <select class="custom-select form-control{{ $errors->has('type') ? ' is-invalid' : '' }}"
                                    name="event_link_type_id">
                                    <option value="">Choose type...</option>
                                    @if ($eventLink->id)
                                        @foreach ($eventLinkTypes as $eventLinkType)
                                            <option value="{{ $eventLinkType->id }}"
                                                {{ old('event_link_type_id', $eventLink->type->id) == $eventLinkType->id ? 'selected' : '' }}>
                                                {{ $eventLinkType->name }}</option>
                                        @endforeach
                                    @else
                                        @foreach ($eventLinkTypes as $eventLinkType)
                                            <option value="{{ $eventLinkType->id }}"
                                                {{ old('event_link_type_id') == $eventLinkType->id ? 'selected' : '' }}>
                                                {{ $eventLinkType->name }}</option>
                                        @endforeach
                                    @endif
                                </select>

                                @if ($errors->has('event_link_type_id'))
                                    <span class="invalid-feedback">
                                        <strong>{{ $errors->first('event_link_type_id') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        {{-- Event --}}
                        <div class="form-group row">
                            <label for="icao_event" class="col-md-4 col-form-label text-md-right">Event</label>

                            <div class="col-md-6">
                                @if ($eventLink->id)
                                    <div class="form-control form-control-plaintext">
                                        {{ $eventLink->event->name }}
                                        [{{ $eventLink->event->startEvent->format('d-m-Y') }}]
                                    </div>
                                @else
                                    <select
                                        class="custom-select form-control{{ $errors->has('event_id') ? ' is-invalid' : '' }}"
                                        name="event_id">
                                        <option value="">Choose an event...</option>
                                        @foreach ($events as $event)
                                            <option value="{{ $event->id }}"
                                                {{ old('event_id') == $event->id ? 'selected' : '' }}>
                                                {{ $event->name }} [{{ $event->startEvent->format('d-m-Y') }}]
                                            </option>
                                        @endforeach
                                    </select>

                                    @if ($errors->has('event_id'))
                                        <span class="invalid-feedback">
                                            <strong>{{ $errors->first('event_id') }}</strong>
                                        </span>
                                    @endif
                                @endif
                            </div>
                        </div>

                        {{-- Name --}}
                        <div class="form-group row">
                            <label for="name" class="col-md-4 col-form-label text-md-right">Name</label>

                            <div class="col-md-6">
                                <input id="name" type="text"
                                    class="form-control{{ $errors->has('name') ? ' is-invalid' : '' }}" name="name"
                                    value="{{ old('name', $eventLink->name) }}">

                                @if ($errors->has('name'))
                                    <span class="invalid-feedback">
                                        <strong>{{ $errors->first('name') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        {{-- URL --}}
                        <div class="form-group row">
                            <label for="url" class="col-md-4 col-form-label text-md-right">URL</label>

                            <div class="col-md-6">
                                <input id="name" type="text"
                                    class="form-control{{ $errors->has('url') ? ' is-invalid' : '' }}" name="url"
                                    value="{{ old('url', $eventLink->url) }}" required>

                                @if ($errors->has('url'))
                                    <span class="invalid-feedback">
                                        <strong>{{ $errors->first('url') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        {{-- Add/Edit --}}
                        <div class="form-group row mb-0">
                            <div class="col-md-6 offset-md-4">
                                <button type="submit" class="btn btn-primary">
                                    @if ($eventLink->id)
                                        <i class="fa fa-check"></i> Edit
                                    @else
                                        <i class="fa fa-plus"></i> Add
                                    @endif
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

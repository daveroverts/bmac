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
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Add new Airport Link</div>

                <div class="card-body">
                    <form method="POST" action="{{ route('airportLinks.store') }}">
                        @csrf
                        {{--Type--}}
                        <div class="form-group row">
                            <label for="airportLinkType_id" class="col-md-4 col-form-label text-md-right">Type</label>

                            <div class="col-md-6">
                                <select class="custom-select form-control{{ $errors->has('type') ? ' is-invalid' : '' }}"
                                        name="airportLinkType_id">
                                    <option value="">Choose type...</option>
                                    @foreach($airportLinkTypes as $airportLinkType)
                                        <option value="{{ $airportLinkType->id }}" {{ old('airportLinkType_id') == $airportLinkType->id ? 'selected' : '' }}>{{ $airportLinkType->name }}</option>
                                    @endforeach
                                </select>

                                @if ($errors->has('airportLinkType_id'))
                                    <span class="invalid-feedback">
                                        <strong>{{ $errors->first('airportLinkType_id') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        {{--Airport--}}
                        <div class="form-group row">
                            <label for="airport_id" class="col-md-4 col-form-label text-md-right">Airport</label>

                            <div class="col-md-6">
                                <select class="custom-select form-control{{ $errors->has('airport_id') ? ' is-invalid' : '' }}"
                                        name="airport_id">
                                    <option value="">Choose an airport...</option>
                                    @foreach($airports as $airport)
                                        <option value="{{ $airport->id }}" {{ old('airport_id') == $airport->id ? 'selected' : '' }}>{{ $airport->icao }}
                                            [{{ $airport->name }} ({{ $airport->iata }})]
                                        </option>
                                    @endforeach
                                </select>

                                @if ($errors->has('airport_id'))
                                    <span class="invalid-feedback">
                                        <strong>{{ $errors->first('airport_id') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        {{--Name--}}
                        <div class="form-group row">
                            <label for="name" class="col-md-4 col-form-label text-md-right">Name</label>

                            <div class="col-md-6">
                                <input id="name" type="text"
                                       class="form-control{{ $errors->has('name') ? ' is-invalid' : '' }}" name="name"
                                       value="{{ old('name') }}">

                                @if ($errors->has('name'))
                                    <span class="invalid-feedback">
                                        <strong>{{ $errors->first('name') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        {{--URL--}}
                        <div class="form-group row">
                            <label for="url" class="col-md-4 col-form-label text-md-right">URL</label>

                            <div class="col-md-6">
                                <input id="name" type="text"
                                       class="form-control{{ $errors->has('url') ? ' is-invalid' : '' }}" name="url"
                                       value="{{ old('url') }}" required>

                                @if ($errors->has('url'))
                                    <span class="invalid-feedback">
                                        <strong>{{ $errors->first('url') }}</strong>
                                    </span>
                                @endif
                            </div>
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

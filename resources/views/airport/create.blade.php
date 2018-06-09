@extends('layouts.app')

@section('content')
        @if (count($errors) > 0)
            <div class = "alert alert-danger">
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
                    <div class="card-header">Add new Airport</div>

                    <div class="card-body">
                        <form method="POST" action="{{ route('airport.store') }}">
                            @csrf
                            {{--ICAO--}}
                            <div class="form-group row">
                                <label for="icao" class="col-md-4 col-form-label text-md-right"> ICAO</label>

                                <div class="col-md-6">
                                    <input id="icao" type="text" class="form-control{{ $errors->has('icao') ? ' is-invalid' : '' }}" name="icao" value="{{ old('icao') }}" required autofocus>

                                    @if ($errors->has('icao'))
                                        <span class="invalid-feedback">
                                        <strong>{{ $errors->first('icao') }}</strong>
                                    </span>
                                    @endif
                                </div>
                            </div>

                            {{--IATA--}}
                            <div class="form-group row">
                                <label for="iata" class="col-md-4 col-form-label text-md-right"> IATA</label>

                                <div class="col-md-6">
                                    <input id="iata" type="text" class="form-control{{ $errors->has('iata') ? ' is-invalid' : '' }}" name="iata" value="{{ old('iata') }}" required>

                                    @if ($errors->has('name'))
                                        <span class="invalid-feedback">
                                        <strong>{{ $errors->first('iata') }}</strong>
                                    </span>
                                    @endif
                                </div>
                            </div>

                            {{--name--}}
                            <div class="form-group row">
                                <label for="name" class="col-md-4 col-form-label text-md-right"> Name</label>

                                <div class="col-md-6">
                                    <input id="name" type="text" class="form-control{{ $errors->has('name') ? ' is-invalid' : '' }}" name="name" value="{{ old('name') }}" required>

                                    @if ($errors->has('name'))
                                        <span class="invalid-feedback">
                                        <strong>{{ $errors->first('name') }}</strong>
                                    </span>
                                    @endif
                                </div>
                            </div>

                            {{--Add--}}
                            <div class="form-group row mb-0">
                                <div class="col-md-6 offset-md-4">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fa fa-user-plus"></i> Add
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
@endsection

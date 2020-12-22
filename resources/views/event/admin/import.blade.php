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
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ $event->name }} | Import</div>

                <div class="card-body">
                    <form method="POST" action="{{ route('admin.bookings.import',$event) }}"
                          enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        {{--File--}}
                        <div class="form-group row">

                            <div class="col-md-4 col-form-label text-md-right"></div>
                            <div class="col-md-6">
                                <input type="file" class="form-control-file" id="file" name="file">

                                @if ($errors->has('file'))
                                    <span class="invalid-feedback">
                                        <strong>{{ $errors->first('file') }}</strong>
                                    </span>
                                @endif

                            </div>
                        </div>

                        <div class="form-group row">

                            <div class="col-md-4 form-control-plaintext text-md-right">
                                Headers in <strong>bold</strong> are mandatory
                            </div>

                            <div class="col-md-8">
                                <div class="form-control-plaintext">
                                    @if($event->event_type_id == \App\Enums\EventType::MULTIFLIGHTS)
                                        <strong><abbr title="[hh:mm]">CTOT 1</abbr></strong> - <strong><abbr title="[ICAO]">Airport 1</abbr></strong> -
                                        <strong><abbr title="[hh:mm]">CTOT 2</abbr></strong> - <strong><abbr title="[ICAO]">Airport 2</abbr></strong> -
                                        <strong><abbr title="[ICAO]">Airport 3</abbr></strong>
                                    @else
                                        Call Sign | <strong><abbr title="[ICAO]">Origin</abbr></strong> |
                                        <strong><abbr title="[ICAO]">Destination</abbr></strong> |
                                        <abbr title="[hh:mm]">CTOT</abbr> | <abbr title="[hh:mm]">ETA</abbr> |
                                        <abbr title="[ICAO]">Aircraft Type</abbr> | Route | Notes

                                    @endif
                                </div>
                            </div>
                        </div>

                        {{--Import--}}
                        <div class="form-group row mb-0">
                            <div class="col-md-6 offset-md-4">
                                <button type="submit" class="btn btn-success">
                                    <i class="fas fa-check"></i> Import
                                </button>
                                <a class="btn btn-secondary" href="{{ url('import_template.xlsx') }}">
                                    <i class="fas fa-file-excel"></i> Download template
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

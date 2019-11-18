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
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">My settings</div>

                <div class="card-body">
                    <form method="POST" action="{{ route('user.saveSettings') }}">
                        @csrf
                        @method('PATCH')

                        {{--Default Airport Name--}}
                        <div class="form-group row">
                            <label for="airport_view" class="col-md-4 col-form-label text-md-right"> <abbr
                                    title="Let's you choose how you want to see airports on the booking pages. Left side shows what you see by default, and on the right (separated with a '-') if you mouse over it">Default
                                    Airport View</abbr></label>

                            <div class="col-md-6">
                                <div class="custom-control custom-control-inline custom-radio">
                                    <input type="radio" value="0" id="airport_view0" name="airport_view"
                                           class="custom-control-input" {{ old('airport_view', $user->airport_view) == 0 ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="airport_view0"><abbr
                                            title="Amsterdam Airport Schiphol - EHAM | [AMS]">Name</abbr></label>
                                </div>
                                <div class="custom-control custom-control-inline custom-radio">
                                    <input type="radio" value="1" id="airport_view1" name="airport_view"
                                           class="custom-control-input" {{ old('airport_view', $user->airport_view) == 1 ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="airport_view1"><abbr
                                            title="EHAM - Amsterdam Airport Schiphol | [AMS]">ICAO</abbr></label>
                                </div>
                                <div class="custom-control custom-control-inline custom-radio">
                                    <input type="radio" value="2" id="airport_view2" name="airport_view"
                                           class="custom-control-input" {{ old('airport_view', $user->airport_view) == 2 ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="airport_view2"><abbr
                                            title="AMS - Amsterdam Airport Schiphol | [EHAM]">IATA</abbr></label>
                                </div>
                            </div>
                        </div>

                        {{--Use monspace font--}}
                        <div class="form-group row">
                            <label for="use_monospace_font" class="col-md-4 col-form-label text-md-right"> <abbr
                                    title="This font will be used on the Callsign and Aircraft in the Slot table">Use
                                    monospace font</abbr></label>

                            <div class="col-md-6">
                                <div class="custom-control custom-control-inline custom-radio">
                                    <input type="radio" value="0" id="use_monospace_font0" name="use_monospace_font"
                                           class="custom-control-input" {{ old('use_monospace_font', $user->use_monospace_font) == 0 ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="use_monospace_font0">No</label>
                                </div>
                                <div class="custom-control custom-control-inline custom-radio">
                                    <input type="radio" value="1" id="use_monospace_font1" name="use_monospace_font"
                                           class="custom-control-input" {{ old('use_monospace_font', $user->use_monospace_font) == 1 ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="use_monospace_font1">Yes</label>
                                </div>
                            </div>
                        </div>

                        {{--Save--}}
                        <div class="form-group row mb-0">
                            <div class="col-md-6 offset-md-4">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Save settings
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

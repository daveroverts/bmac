@extends('layouts.app')

@section('content')
    <x-forms.alert />
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ $airport->name }} [{{ $airport->icao }} | {{ $airport->iata }}]</div>

                <div class="card-body">
                    @foreach($airport->links as $link)
                        <div class="form-group row">
                            <label for="{{ $link->type->name . '-' . $loop->index }}"
                                   class="col-md-4 col-form-label text-md-right">{{ $link->name ?? $link->type->name }}</label>

                            <div class="col-md-6">
                                <div class="form-control-plaintext"><a
                                        href="{{ $link->url }}"
                                        target="_blank">Link</a></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
@endsection

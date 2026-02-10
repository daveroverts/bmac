@extends('layouts.app')

@section('content')
    <x-forms.alert />
    @include('layouts.alert')
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ $faq->id ? 'Edit' : 'Add new' }} FAQ</div>

                <div class="card-body">
                    <x-form :action="$faq->id ? route('admin.faq.update', $faq) : route('admin.faq.store')" :method="$faq->id ? 'PATCH' : 'POST'">

                            <x-forms.form-group name="is_online" :label="__('Is online')" inline>

                                <x-forms.radio inline name="is_online" value="0" :label="__('No')" required :should-be-checked="old('is_online' === false, !$faq->is_online )" />
                                <x-forms.radio inline name="is_online" value="1" :label="__('Yes')" required :should-be-checked="old('is_online' === true, $faq->is_online )" />

                            </x-forms.form-group>

                            <x-forms.input name="question" :label="__('Question')" required value="{!! old('question', $faq->question) !!}" />

                            <x-forms.textarea tinymce name="answer" :label="__('Answer')" value="{!! old('answer', $faq->answer) !!}" />

                        <x-forms.button type="submit">
                                @if ($faq->id)
                                    <i class="fa fa-check"></i> {{ __('Edit') }}
                                @else
                                    <i class="fa fa-plus"></i> {{ __('Add') }}
                                @endif
                            </x-forms.button>
                    </x-form>

                </div>

                @if ($faq->id)
                    <div class="card-header">{{ __('Related events') }}</div>
                    @if ($events)
                        <div class="card-body">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th scope="row">{{ __('Name') }}</th>
                                        <th scope="row">{{ __('Actions') }}</th>
                                    </tr>
                                </thead>
                                @foreach ($events as $event)
                                    <tr>
                                        <td>{{ $event->name }} [{{ $event->startEvent->format('d-m-Y') }}]</td>
                                        <td>
                                            <form
                                                action="{{ route('admin.faq.toggleEvent', ['faq' => $faq, 'event' => $event]) }}"
                                                method="post">
                                                @csrf
                                                @method('PATCH')

                                                @if ($faq->events()->where('event_id', $event->id)->first())
                                                    <x-confirm-button
                                                        confirm-text="Are you sure you want to unlink this event?"
                                                        loading-message="Unlinking event..."
                                                    >{{ __('Unlink event') }}</x-confirm-button>
                                                @else
                                                    <button class="btn btn-success">{{ __('Link event') }}</button>
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

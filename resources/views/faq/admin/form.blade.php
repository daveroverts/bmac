@extends('layouts.app')

@section('content')
    <x-forms.alert />
    @include('layouts.alert')
    @push('scripts')
        <script>
            $('.unlink-event').on('click', function(e) {
                e.preventDefault();
                Swal.fire({
                    title: 'Are you sure',
                    text: 'Are you sure you want to unlink this event?',
                    icon: 'warning',
                    showCancelButton: true,
                }).then((result) => {
                    if (result.value) {
                        Swal.fire('Unlinking event...');
                        Swal.showLoading();
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
                    <x-form :action="$faq->id ? route('admin.faq.update', $faq) : route('admin.faq.store')" :method="$faq->id ? 'PATCH' : 'POST'">

                            <x-forms.form-group name="is_online" :label="__('Is online')" inline>
                                <x-forms.radio name="is_online" value="0" :label="__('No')" required />
                                <x-forms.radio name="is_online" value="1" :label="__('Yes')" required />
                            </x-forms.form-group>

                            <x-forms.input name="question" :label="__('Question')" required />

                            <x-forms.textarea name="answer" :label="__('Answer')" class="tinymce" />

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
                                                    <button
                                                        class="btn btn-danger unlink-event">{{ __('Unlink event') }}</button>
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

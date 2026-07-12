<div>
    @php($isMulti = $event->event_type_id == \App\Enums\EventType::MULTIFLIGHTS->value)

    <div class="mb-3">
        <label for="file" class="form-label">{{ __('File') }}</label>
        <input type="file" id="file" class="form-control" wire:model="file" accept=".csv,.xlsx">
        <div class="form-text">{{ __('Accepted formats: CSV, XLSX. Max size: 10 MB.') }}</div>
        @error('file')
            <div class="text-danger">{{ $message }}</div>
        @enderror
        <div wire:loading wire:target="file" class="text-muted mt-1">{{ __('Parsing file...') }}</div>
    </div>

    @if ($autoAddedAirports !== [])
        <div class="alert alert-info">
            {{ __('Automatically added :count missing airport(s):', ['count' => count($autoAddedAirports)]) }}
            {{ implode(', ', $autoAddedAirports) }}
        </div>
    @endif

    @if ($rows !== [])
        <div class="d-flex gap-2 mb-2">
            <button type="button" class="btn btn-sm btn-outline-secondary" wire:click="selectAll">
                {{ __('Select all') }}
            </button>
            <button type="button" class="btn btn-sm btn-outline-secondary" wire:click="selectNone">
                {{ __('Select none') }}
            </button>
            <span class="ms-auto align-self-center text-muted">
                {{ __(':selected of :total selected', ['selected' => count($selected), 'total' => count($rows)]) }}
            </span>
        </div>

        <table class="table table-hover align-middle">
            <thead>
                <tr>
                    <th scope="col"></th>
                    @if ($isMulti)
                        <th scope="col">{{ __('Airport 1') }}</th>
                        <th scope="col">{{ __('Airport 2') }}</th>
                        <th scope="col">{{ __('Airport 3') }}</th>
                    @else
                        <th scope="col">{{ __('Origin') }}</th>
                        <th scope="col">{{ __('Destination') }}</th>
                        <th scope="col">{{ __('Call sign') }}</th>
                        <th scope="col">{{ __('Aircraft') }}</th>
                    @endif
                    <th scope="col">{{ __('Issues') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($rows as $index => $row)
                    <tr @class(['table-danger' => ! $row['valid']])>
                        <td>
                            <input
                                type="checkbox"
                                class="form-check-input"
                                wire:model.live="selected"
                                value="{{ $index }}"
                                @disabled(! $row['valid'])
                            >
                        </td>
                        @if ($isMulti)
                            <td>{{ $row['data']['airport_1'] ?? '' }}</td>
                            <td>{{ $row['data']['airport_2'] ?? '' }}</td>
                            <td>{{ $row['data']['airport_3'] ?? '' }}</td>
                        @else
                            <td>{{ $row['data']['origin'] ?? '' }}</td>
                            <td>{{ $row['data']['destination'] ?? '' }}</td>
                            <td>{{ $row['data']['call_sign'] ?? '' }}</td>
                            <td>{{ $row['data']['aircraft_type'] ?? '' }}</td>
                        @endif
                        <td>
                            @forelse ($row['errors'] as $error)
                                <span class="badge bg-danger">{{ $error }}</span>
                            @empty
                                <span class="badge bg-success">{{ __('OK') }}</span>
                            @endforelse
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <button type="button" class="btn btn-primary" wire:click="import" @disabled($selected === [])>
            <i class="fas fa-check"></i> {{ __('Import selected') }}
        </button>
    @endif
</div>

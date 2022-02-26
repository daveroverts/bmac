<x-filament::page>
    <x-filament::form wire:submit.prevent="submit">
        {{ $this->form }}

        <div class="flex flex-wrap items-center gap-4 filament-page-actions justify-start">
            <x-filament::button type="submit">
                Submit
            </x-filament::button>
            <x-filament::button tag="a" icon="heroicon-o-document-download" color="secondary"
                href="{{ $event->event_type_id == \App\Enums\EventType::MULTIFLIGHTS? url('import_multi_flights_template.xlsx'): url('import_template.xlsx') }}">
                Download template
            </x-filament::button>
        </div>

    </x-filament::form>
</x-filament::page>

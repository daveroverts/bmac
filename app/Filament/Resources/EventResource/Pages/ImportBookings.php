<?php

namespace App\Filament\Resources\EventResource\Pages;

use App\Enums\EventType;
use App\Models\Event;
use App\Imports\BookingsImport;
use Filament\Resources\Pages\Page;
use Filament\Forms\Contracts\HasForms;
use Illuminate\Support\Facades\Storage;
use App\Filament\Resources\EventResource;
use App\Models\File;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Concerns\InteractsWithForms;
use Illuminate\Database\Eloquent\Model;

class ImportBookings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string $resource = EventResource::class;

    protected static string $view = 'filament.resources.event-resource.pages.import-bookings';

    public Event $event;

    public function mount(Event $record): void
    {
        $this->event = $record;
        $this->form->fill();
    }

    protected function getFormSchema(): array
    {
        $helperText = 'Headers in **bold** are mandatory<br />';
        switch ($this->event->type) {
            case EventType::MULTIFLIGHTS():
                $helperText .= '**<abbr title="[hh:mm]">CTOT 1</abbr>** - **<abbr title="[ICAO]">Airport 1</abbr>** - **<abbr title="[hh:mm]">CTOT 2</abbr>** - **<abbr title="[ICAO]">Airport 2</abbr>** - **<abbr title="[ICAO]">Airport 3</abbr>**';
                break;
            default:
                $helperText .= 'Call Sign | **<abbr title="[ICAO]">Origin</abbr>** | **<abbr title="[ICAO]">Destination</abbr>** | <abbr title="[hh:mm]">CTOT</abbr> | <abbr title="[hh:mm]">ETA</abbr> | <abbr title="[ICAO]">Aircraft Type</abbr> | Route | Notes | Track | <abbr title="Max 3 numbers. Examples: 370">FL</abbr>';
        }
        return [
            FileUpload::make('file')
                ->helperText($helperText)
                ->required()
                ->directory('imports')
                ->visibility('private'),
        ];
    }

    protected function getFormModel(): Model|string|null
    {
        return $this->event;
    }

    public function submit(): void
    {
        activity()
            ->by(auth()->user())
            ->on($this->event)
            ->log('Import triggered');

        $this->event->files()->create([
            'path' => $this->form->getState()['file'],
            'type' => BookingsImport::class,
        ]);

        $this->notify('success', 'Import triggered', true);
        $this->redirectRoute('filament.resources.events.index');
    }
}

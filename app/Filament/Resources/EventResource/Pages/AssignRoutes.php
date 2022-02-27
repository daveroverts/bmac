<?php

namespace App\Filament\Resources\EventResource\Pages;

use App\Models\Event;
use App\Imports\FlightRouteAssign;
use Filament\Resources\Pages\Page;
use Filament\Forms\Contracts\HasForms;
use Illuminate\Database\Eloquent\Model;
use App\Filament\Resources\EventResource;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Concerns\InteractsWithForms;

class AssignRoutes extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string $resource = EventResource::class;

    protected static string $view = 'filament.resources.event-resource.pages.assign-routes';

    public Event $event;

    public function mount(Event $record): void
    {
        $this->event = $record;
        $this->form->fill();
    }

    protected function getFormSchema(): array
    {
        return [
            FileUpload::make('file')
                ->helperText('Headers in **bold** are mandatory<br />**<abbr title="[ICAO]">From</abbr>** | **<abbr title="[ICAO]">To</abbr>** | **Route** | Notes')
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
            'disk' => config('filament.default_filesystem_disk'),
            'type' => FlightRouteAssign::class,
        ]);

        $this->notify('success', 'Import triggered', true);
        $this->redirectRoute('filament.resources.events.index');
    }
}

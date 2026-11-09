<?php

namespace App\Filament\Resources\Airports\Pages;

use App\Filament\Resources\Airports\AirportResource;
use App\Models\Airport;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;

class ListAirports extends ListRecords
{
    protected static string $resource = AirportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('deleteUnused')
                ->label(__('Delete unused airports'))
                ->icon(Heroicon::OutlinedTrash)
                ->color('gray')
                ->requiresConfirmation()
                ->modalDescription(__('This deletes every airport that is not linked to an event or flight.'))
                ->action(function (): void {
                    $count = Airport::whereDoesntHave('flightsDep')
                        ->whereDoesntHave('flightsArr')
                        ->whereDoesntHave('eventDep')
                        ->whereDoesntHave('eventArr')
                        ->delete();

                    Notification::make()
                        ->title(__(':count unused airport(s) deleted', ['count' => $count]))
                        ->success()
                        ->send();
                }),
            CreateAction::make()
                ->icon(Heroicon::OutlinedPlus),
        ];
    }
}

<?php

namespace App\Filament\Resources\Airports\Pages;

use App\Filament\Resources\Airports\AirportResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewAirport extends ViewRecord
{
    protected static string $resource = AirportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}

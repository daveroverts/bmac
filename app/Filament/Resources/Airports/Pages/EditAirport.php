<?php

namespace App\Filament\Resources\Airports\Pages;

use App\Filament\Resources\Airports\AirportResource;
use App\Models\Airport;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditAirport extends EditRecord
{
    protected static string $resource = AirportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make()
                ->hidden(fn (Airport $record): bool => AirportResource::isInUse($record)),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }
}

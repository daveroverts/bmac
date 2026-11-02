<?php

namespace App\Filament\Resources\Airports;

use App\Filament\Resources\Airports\Pages\CreateAirport;
use App\Filament\Resources\Airports\Pages\EditAirport;
use App\Filament\Resources\Airports\Pages\ListAirports;
use App\Filament\Resources\Airports\Pages\ViewAirport;
use App\Filament\Resources\Airports\RelationManagers\LinksRelationManager;
use App\Filament\Resources\Airports\Schemas\AirportForm;
use App\Filament\Resources\Airports\Schemas\AirportInfolist;
use App\Filament\Resources\Airports\Tables\AirportsTable;
use App\Models\Airport;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class AirportResource extends Resource
{
    protected static ?string $model = Airport::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMapPin;

    protected static ?string $recordTitleAttribute = 'name';

    public static function getRecordTitle(?Model $record): string
    {
        /** @var Airport|null $record */
        return $record instanceof Airport ? sprintf('%s [%s | %s]', $record->name, $record->icao, $record->iata) : '';
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['icao', 'iata', 'name'];
    }

    /**
     * Airports in use by a flight or event cannot be deleted.
     *
     * @return Builder<Model>
     */
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withCount(['flightsDep', 'flightsArr', 'eventDep', 'eventArr', 'links']);
    }

    public static function isInUse(Airport $airport): bool
    {
        return ($airport->flights_dep_count ?? $airport->flightsDep()->count()) > 0
            || ($airport->flights_arr_count ?? $airport->flightsArr()->count()) > 0
            || ($airport->event_dep_count ?? $airport->eventDep()->count()) > 0
            || ($airport->event_arr_count ?? $airport->eventArr()->count()) > 0;
    }

    public static function form(Schema $schema): Schema
    {
        return AirportForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return AirportInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AirportsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            LinksRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAirports::route('/'),
            'create' => CreateAirport::route('/create'),
            'view' => ViewAirport::route('/{record}'),
            'edit' => EditAirport::route('/{record}/edit'),
        ];
    }
}

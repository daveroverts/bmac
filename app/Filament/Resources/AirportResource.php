<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Airport;
use Filament\Resources\Form;
use Filament\Resources\Table;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Illuminate\Database\Eloquent\Model;
use App\Filament\Resources\AirportResource\Pages;
use App\Filament\Resources\AirportResource\RelationManagers;
use Filament\Tables\Actions\ButtonAction;

class AirportResource extends Resource
{
    protected static ?string $model = Airport::class;

    protected static ?string $navigationIcon = 'heroicon-o-paper-airplane';

    public static ?string $recordTitleAttribute = 'name';

    public static function getGloballySearchableAttributes(): array
    {
        return ['icao', 'iata', 'name'];
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            'ICAO' => $record->icao,
            'IATA' => $record->iata,
        ];
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('icao')
                    ->label('ICAO')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('iata')
                    ->label('IATA')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('latitude'),
                Forms\Components\TextInput::make('longitude'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('icao')->label('ICAO')->sortable(),
                Tables\Columns\TextColumn::make('iata')->label('IATA')->sortable(),
                Tables\Columns\TextColumn::make('name')->sortable(),
            ])
            ->defaultSort('icao')
            ->filters([
                //
            ])
            ->pushHeaderActions([
                ButtonAction::make('delete-unused-airports')
                    ->action(function () {
                        Airport::whereDoesntHave('flightsDep')
                            ->whereDoesntHave('flightsArr')
                            ->whereDoesntHave('eventDep')
                            ->whereDoesntHave('eventArr')
                            ->delete();
                    })
                    ->requiresConfirmation()
                    ->color('danger')
                    ->icon('heroicon-o-trash')
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\LinksRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAirports::route('/'),
            'create' => Pages\CreateAirport::route('/create'),
            'edit' => Pages\EditAirport::route('/{record}/edit'),
        ];
    }
}

<?php

namespace App\Filament\Resources\Airports\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class AirportForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('Airport'))
                    ->columns(3)
                    ->schema([
                        TextInput::make('icao')
                            ->label(__('ICAO'))
                            ->validationAttribute(__('ICAO'))
                            ->required()
                            ->length(4)
                            ->unique(ignoreRecord: true)
                            ->extraInputAttributes(['class' => 'uppercase']),
                        TextInput::make('iata')
                            ->label(__('IATA'))
                            ->validationAttribute(__('IATA'))
                            ->required()
                            ->length(3)
                            ->unique(ignoreRecord: true)
                            ->extraInputAttributes(['class' => 'uppercase']),
                        TextInput::make('name')
                            ->label(__('Name'))
                            ->required(),
                    ]),
                Section::make(__('Position'))
                    ->description(__('Decimal degrees, used for the map and distance calculations.'))
                    ->columns(2)
                    ->schema([
                        TextInput::make('latitude')
                            ->label(__('Latitude'))
                            ->required()
                            ->numeric()
                            ->minValue(-90)
                            ->maxValue(90)
                            ->placeholder('52.308601'),
                        TextInput::make('longitude')
                            ->label(__('Longitude'))
                            ->required()
                            ->numeric()
                            ->minValue(-180)
                            ->maxValue(180)
                            ->placeholder('4.76389'),
                    ]),
            ])
            ->columns(1);
    }
}

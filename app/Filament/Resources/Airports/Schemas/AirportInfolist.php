<?php

namespace App\Filament\Resources\Airports\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class AirportInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('Airport'))
                    ->columns(3)
                    ->schema([
                        TextEntry::make('icao')->label(__('ICAO'))->badge(),
                        TextEntry::make('iata')->label(__('IATA'))->badge()->color('gray'),
                        TextEntry::make('name')->label(__('Name')),
                        TextEntry::make('latitude')->label(__('Latitude'))->placeholder('-'),
                        TextEntry::make('longitude')->label(__('Longitude'))->placeholder('-'),
                        TextEntry::make('updated_at')->label(__('Last updated'))->since()->placeholder('-'),
                    ]),
                Section::make(__('Usage'))
                    ->columns(4)
                    ->schema([
                        TextEntry::make('event_dep_count')->label(__('Events (departure)'))->numeric(),
                        TextEntry::make('event_arr_count')->label(__('Events (arrival)'))->numeric(),
                        TextEntry::make('flights_dep_count')->label(__('Flights (departure)'))->numeric(),
                        TextEntry::make('flights_arr_count')->label(__('Flights (arrival)'))->numeric(),
                    ]),
            ])
            ->columns(1);
    }
}

<?php

namespace App\Filament\Resources\EventResource\RelationManagers;

use Filament\Forms;
use Filament\Tables;
use App\Enums\BookingStatus;
use Filament\Resources\Form;
use Filament\Resources\Table;
use Filament\Tables\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;
use Filament\Resources\RelationManagers\HasManyRelationManager;
use Filament\Resources\RelationManagers\RelationManager;
use Illuminate\Database\Eloquent\Model;

class BookingsRelationManager extends HasManyRelationManager
{
    protected static string $relationship = 'bookings';

    protected static ?string $recordTitleAttribute = 'id';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // Forms\Components\TextInput::make('callsign')->required()
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('firstFlight.ctot')->label('CTOT')->time('Hi\z'),
                Tables\Columns\TextColumn::make('firstFlight.eta')->label('ETA')->time('Hi\z'),
                Tables\Columns\TextColumn::make('firstFlight.airportDep.icao')->label('DEP'),
                Tables\Columns\TextColumn::make('firstFlight.airportArr.icao')->label('ARR'),
                Tables\Columns\TextColumn::make('callsign'),
                Tables\Columns\TextColumn::make('acType')->label('Aircraft type'),
                Tables\Columns\BadgeColumn::make('status')
                    ->enum([
                        BookingStatus::UNASSIGNED()->value => 'Unassigned',
                        BookingStatus::RESERVED()->value => 'Reserved',
                        BookingStatus::BOOKED()->value => 'Booked',
                    ])
                    ->colors([
                        'warning' => BookingStatus::RESERVED()->value,
                        'success' => BookingStatus::BOOKED()->value,
                    ]),
                Tables\Columns\TextColumn::make('user.pic')->label('PIC'),
            ])
            ->filters([
                //
            ]);
    }
}

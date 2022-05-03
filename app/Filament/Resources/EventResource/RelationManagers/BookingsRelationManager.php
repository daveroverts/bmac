<?php

namespace App\Filament\Resources\EventResource\RelationManagers;

use Filament\Forms;
use Filament\Tables;
use App\Models\Airport;
use App\Enums\BookingStatus;
use Filament\Resources\Form;
use Filament\Resources\Table;
use Filament\Tables\Filters\Filter;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Resources\RelationManagers\HasManyRelationManager;

class BookingsRelationManager extends HasManyRelationManager
{
    protected static string $relationship = 'bookings';

    protected static ?string $recordTitleAttribute = 'id';

    protected function canCreate(): bool
    {
        if ($this->ownerRecord->event_type_id == EventType::MULTIFLIGHTS) {
            return false;
        }

        return parent::canCreate();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Toggle::make('is_editable')
                    ->columnSpan('full')
                    ->label('Editable?')
                    ->helperText("Choose if you want the booking to be editable (Callsign and Aircraft Code only) by users. This is useful when using 'import only', but want to add extra slots")
                    ->required(),
                Forms\Components\TextInput::make('callsign')
                    ->maxLength(7),
                Forms\Components\TextInput::make('acType')
                    ->label('Aircraft code')
                    ->maxLength(4),
                Forms\Components\HasManyRepeater::make('flights')
                    ->relationship('flights')
                    ->label(__('Flight'))
                    ->columnSpan('full')
                    ->columns(2)
                    ->disableItemCreation()
                    ->disableItemDeletion()
                    ->disableItemMovement()
                    ->schema([
                        Forms\Components\Select::make('dep')
                            ->label('Departure airport')
                            ->required()
                            ->searchable()
                            ->getSearchResultsUsing(fn (string $query) => Airport::where('icao', 'like', "%{$query}%")
                                ->orWhere('iata', 'like', "%{$query}%")->orWhere('name', 'like', "%{$query}%")
                                ->limit(50)->get()->mapWithKeys(fn (Airport $airport) => [$airport->id => "$airport->icao | $airport->iata | $airport->name"]))
                            ->getOptionLabelUsing(function (?string $value) {
                                $airport = Airport::find($value);
                                return $airport ? "{$airport?->icao} | {$airport?->iata} | {$airport?->name}" : '';
                            }),
                        Forms\Components\Select::make('arr')
                            ->label('Arrival airport')
                            ->required()
                            ->searchable()
                            ->getSearchResultsUsing(fn (string $query) => Airport::where('icao', 'like', "%{$query}%")
                                ->orWhere('iata', 'like', "%{$query}%")->orWhere('name', 'like', "%{$query}%")
                                ->limit(50)->get()->mapWithKeys(fn (Airport $airport) => [$airport->id => "$airport->icao | $airport->iata | $airport->name"]))
                            ->getOptionLabelUsing(function (?string $value) {
                                $airport = Airport::find($value);
                                return $airport ? "{$airport?->icao} | {$airport?->iata} | {$airport?->name}" : '';
                            }),
                        Forms\Components\TimePicker::make('ctot')->label('CTOT (UTC)')->withoutSeconds(),
                        Forms\Components\TimePicker::make('eta')->label('ETA (UTC)')->withoutSeconds(),
                        Forms\Components\TextInput::make('oceanicFL')
                            ->label('Cruise FL')
                            ->prefix('FL')
                            ->numeric()
                            ->step(10)
                            ->minLength(0)
                            ->maxLength(660),
                        Forms\Components\Textarea::make('route')->columnSpan('full'),
                        Forms\Components\Textarea::make('notes')->columnSpan('full'),
                    ])
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

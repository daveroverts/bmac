<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EventResource\Pages;
use App\Filament\Resources\EventResource\RelationManagers;
use App\Models\Airport;
use App\Models\Event;
use App\Models\EventType;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;

class EventResource extends Resource
{
    protected static ?string $model = Event::class;

    protected static ?string $navigationIcon = 'heroicon-o-collection';

    public static ?string $recordTitleAttribute = 'name';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Toggle::make('is_online')
                    ->label('Show online?')
                    ->helperText("Choose here if you want the event to be reachable by it's generated url")
                    ->required(),
                Forms\Components\Toggle::make('show_on_homepage')
                    ->label('Show on homepage?')
                    ->helperText("Choose here if you want to show the event on the homepage. If turned off, the event can only be reached by the url. NOTE: If 'Show Online' is off, the event won't be shown at all")
                    ->required(),
                Forms\Components\Toggle::make('import_only')
                    ->label('Only import?')
                    ->helperText('If enabled, only admins can fill in details via import script')
                    ->required(),
                Forms\Components\Toggle::make('uses_times')
                    ->label('Show times?')
                    ->helperText('If enabled, CTOT and ETA (if set in booking) will be shown')
                    ->required(),
                Forms\Components\Toggle::make('multiple_bookings_allowed')
                    ->label('Multiple bookings allowed?')
                    ->helperText('If enabled, a user is allowed to book multiple flights for this event')
                    ->required(),
                Forms\Components\Toggle::make('is_oceanic_event')
                    ->label('Oceanic event?')
                    ->helperText('If enabled, users can fill in a SELCAL code')
                    ->required(),
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('event_type_id')
                    ->label('Event type')
                    ->options(EventType::all(['id', 'name'])->pluck('name', 'id'))
                    ->required(),
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
                Forms\Components\DateTimePicker::make('startEvent')
                    ->label('Start event (UTC)')
                    ->withoutSeconds()
                    ->required(),
                Forms\Components\DateTimePicker::make('endEvent')
                    ->label('End event (UTC)')
                    ->withoutSeconds()
                    ->required()
                    ->after('startEvent'),
                Forms\Components\DateTimePicker::make('startBooking')
                    ->label('Start booking (UTC)')
                    ->withoutSeconds()
                    ->required(),
                Forms\Components\DateTimePicker::make('endBooking')
                    ->label('End booking (UTC)')
                    ->withoutSeconds()
                    ->required()
                    ->after('startBooking')
                    ->beforeOrEqual('endEvent'),
                Forms\Components\TextInput::make('image_url')
                    ->label('Image URL')
                    ->maxLength(255)
                    ->url(),
                Forms\Components\RichEditor::make('description')
                    ->columnSpan('full'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name'),
                Tables\Columns\TextColumn::make('type.name'),
                Tables\Columns\TextColumn::make('startEvent')
                    ->dateTime('M j, Y H:i')->sortable(),
            ])->defaultSort('startEvent')
            ->filters([
                //
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEvents::route('/'),
            'create' => Pages\CreateEvent::route('/create'),
            'edit' => Pages\EditEvent::route('/{record}/edit'),
        ];
    }
}

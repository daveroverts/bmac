<?php

namespace App\Filament\Resources\Events\Schemas;

use App\Models\Airport;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class EventForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make(__('Event'))
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label(__('Name'))
                            ->required(),
                        Select::make('event_type_id')
                            ->label(__('Event type'))
                            ->relationship('type', 'name')
                            ->required()
                            ->native(false),
                        self::airportSelect('dep', 'airportDep', __('Departure airport')),
                        self::airportSelect('arr', 'airportArr', __('Arrival airport')),
                        TextInput::make('image_url')
                            ->label(__('Image URL'))
                            ->url()
                            ->columnSpanFull(),
                    ]),
                Section::make(__('Schedule'))
                    ->description(__('All times are in UTC (z).'))
                    ->schema([
                        Grid::make(2)->schema([
                            self::dateTime('startEvent', __('Start event')),
                            self::dateTime('endEvent', __('End event'))
                                ->afterOrEqual('startEvent'),
                            self::dateTime('startBooking', __('Start booking')),
                            self::dateTime('endBooking', __('End booking'))
                                ->afterOrEqual('startBooking'),
                        ]),
                    ]),
                Section::make(__('Options'))
                    ->columns(2)
                    ->schema([
                        Toggle::make('is_online')
                            ->label(__('Show online?'))
                            ->helperText(__("Choose here if you want the event to be reachable by it's generated url")),
                        Toggle::make('show_on_homepage')
                            ->label(__('Show on homepage?'))
                            ->helperText(__('If turned off, the event can only be reached by the url. If "Show online" is off, the event is not shown at all.')),
                        Toggle::make('import_only')
                            ->label(__('Only import?'))
                            ->helperText(__('If enabled, only admins can fill in details via import script')),
                        Toggle::make('uses_times')
                            ->label(__('Show times?'))
                            ->helperText(__('If enabled, CTOT and ETA (if set in booking) will be shown')),
                        Toggle::make('multiple_bookings_allowed')
                            ->label(__('Multiple bookings allowed?'))
                            ->helperText(__('If enabled, a user is allowed to book multiple flights for this event')),
                        Toggle::make('is_oceanic_event')
                            ->label(__('Oceanic event?'))
                            ->helperText(__('If enabled, users can fill in a SELCAL code, and oceanic links are shown in the booking briefing')),
                    ]),
                Section::make(__('Description'))
                    ->schema([
                        RichEditor::make('description')
                            ->hiddenLabel()
                            ->required(),
                    ]),
            ]);
    }

    public static function airportSelect(string $name, string $relationship, string $label): Select
    {
        return Select::make($name)
            ->label($label)
            ->relationship($relationship, 'icao')
            ->getOptionLabelFromRecordUsing(fn (Airport $record): string => $record->dropdownLabel())
            ->searchable(['icao', 'iata', 'name'])
            ->preload(false)
            ->required();
    }

    private static function dateTime(string $name, string $label): DateTimePicker
    {
        return DateTimePicker::make($name)
            ->label($label)
            ->required()
            ->seconds(false)
            ->native(false)
            ->displayFormat('D j M Y, H:i')
            ->suffix('z')
            ->timezone('UTC');
    }
}

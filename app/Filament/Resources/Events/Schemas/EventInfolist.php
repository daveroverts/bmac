<?php

namespace App\Filament\Resources\Events\Schemas;

use App\Enums\BookingStatus;
use App\Enums\EventType;
use App\Filament\Resources\Airports\AirportResource;
use App\Models\Event;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;

class EventInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                Section::make(__('Event'))
                    ->columnSpan(2)
                    ->columns(2)
                    ->schema([
                        TextEntry::make('name')
                            ->label(__('Name'))
                            ->url(fn (Event $record): string => route('events.show', $record), shouldOpenInNewTab: true)
                            ->color('primary'),
                        TextEntry::make('type.name')
                            ->label(__('Type'))
                            ->badge(),
                        TextEntry::make('airportDep.icao')
                            ->label(__('Departure airport'))
                            ->formatStateUsing(fn (Event $record): string => sprintf('%s [%s | %s]', $record->airportDep->name, $record->airportDep->icao, $record->airportDep->iata))
                            ->url(fn (Event $record): string => AirportResource::getUrl('view', ['record' => $record->airportDep]))
                            ->hidden(fn (Event $record): bool => $record->event_type_id == EventType::MULTIFLIGHTS->value),
                        TextEntry::make('airportArr.icao')
                            ->label(__('Arrival airport'))
                            ->formatStateUsing(fn (Event $record): string => sprintf('%s [%s | %s]', $record->airportArr->name, $record->airportArr->icao, $record->airportArr->iata))
                            ->url(fn (Event $record): string => AirportResource::getUrl('view', ['record' => $record->airportArr]))
                            ->hidden(fn (Event $record): bool => $record->event_type_id == EventType::MULTIFLIGHTS->value),
                        TextEntry::make('startEvent')
                            ->label(__('Event date | time'))
                            ->formatStateUsing(fn (Event $record): string => sprintf(
                                '%s | %sz - %sz',
                                $record->startEvent->toFormattedDateString(),
                                $record->startEvent->format('Hi'),
                                $record->endEvent->format('Hi'),
                            )),
                        TextEntry::make('booking_status')
                            ->label(__('Booking status'))
                            ->state(fn (Event $record): string => self::bookingStatus($record))
                            ->badge()
                            ->color(fn (Event $record): string => match (true) {
                                $record->startBooking >= now() => 'gray',
                                $record->endBooking <= now() => 'danger',
                                default => 'success',
                            }),
                        TextEntry::make('description')
                            ->label(__('Description'))
                            ->html()
                            ->columnSpanFull()
                            ->formatStateUsing(fn (?string $state): HtmlString => new HtmlString((string) $state)),
                    ]),
                Section::make(__('Bookings'))
                    ->columnSpan(1)
                    ->schema([
                        TextEntry::make('bookings_total')
                            ->label(__('Slots'))
                            ->state(fn (Event $record): int => $record->bookings()->count()),
                        TextEntry::make('bookings_booked')
                            ->label(__('Booked'))
                            ->state(fn (Event $record): int => $record->bookings()->where('status', BookingStatus::BOOKED)->count())
                            ->color('success'),
                        TextEntry::make('bookings_reserved')
                            ->label(__('Reserved'))
                            ->state(fn (Event $record): int => $record->bookings()->where('status', BookingStatus::RESERVED)->count())
                            ->color('warning'),
                    ]),
                Section::make(__('Options'))
                    ->columnSpanFull()
                    ->columns(6)
                    ->schema([
                        IconEntry::make('is_online')->label(__('Show online?'))->boolean(),
                        IconEntry::make('show_on_homepage')->label(__('Show on homepage?'))->boolean(),
                        IconEntry::make('import_only')->label(__('Only import?'))->boolean(),
                        IconEntry::make('uses_times')->label(__('Show times?'))->boolean(),
                        IconEntry::make('multiple_bookings_allowed')->label(__('Multiple bookings allowed?'))->boolean(),
                        IconEntry::make('is_oceanic_event')->label(__('Oceanic event?'))->boolean(),
                    ]),
            ]);
    }

    private static function bookingStatus(Event $event): string
    {
        $format = fn ($date): string => $date->toFormattedDateString() . ' ' . $date->format('Hi') . 'z';

        return match (true) {
            $event->startBooking >= now() => __('Opens :date', ['date' => $format($event->startBooking)]),
            $event->endBooking <= now() => __('Locked since :date', ['date' => $format($event->endBooking)]),
            default => __('Open until :date', ['date' => $format($event->endBooking)]),
        };
    }
}

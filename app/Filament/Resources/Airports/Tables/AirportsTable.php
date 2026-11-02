<?php

namespace App\Filament\Resources\Airports\Tables;

use App\Filament\Resources\Airports\AirportResource;
use App\Models\Airport;
use Filament\Actions\BulkAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class AirportsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            // The model's global "order by icao" scope would override the column sorting.
            ->modifyQueryUsing(fn (Builder $query) => $query->withoutGlobalScope('order'))
            ->defaultSort('icao')
            ->defaultPaginationPageOption(50)
            ->columns([
                TextColumn::make('icao')
                    ->label(__('ICAO'))
                    ->badge()
                    ->searchable()
                    ->sortable(),
                TextColumn::make('iata')
                    ->label(__('IATA'))
                    ->badge()
                    ->color('gray')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('name')
                    ->label(__('Name'))
                    ->searchable()
                    ->sortable()
                    ->wrap(),
                TextColumn::make('events_count')
                    ->label(__('Events'))
                    ->state(fn (Airport $record): int => $record->event_dep_count + $record->event_arr_count)
                    ->alignCenter(),
                TextColumn::make('flights_count')
                    ->label(__('Flights'))
                    ->state(fn (Airport $record): int => $record->flights_dep_count + $record->flights_arr_count)
                    ->alignCenter(),
                TextColumn::make('links_count')
                    ->label(__('Links'))
                    ->alignCenter()
                    ->toggleable(),
                TextColumn::make('latitude')
                    ->label(__('Latitude'))
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('longitude')
                    ->label(__('Longitude'))
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label(__('Last updated'))
                    ->since()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('in_use')
                    ->label(__('In use'))
                    ->trueLabel(__('Used by an event or flight'))
                    ->falseLabel(__('Unused'))
                    ->queries(
                        true: fn (Builder $query) => $query->where(fn (Builder $query) => $query
                            ->has('flightsDep')->orHas('flightsArr')->orHas('eventDep')->orHas('eventArr')),
                        false: fn (Builder $query) => $query
                            ->doesntHave('flightsDep')->doesntHave('flightsArr')->doesntHave('eventDep')->doesntHave('eventArr'),
                    ),
            ])
            ->recordActions([
                ViewAction::make()->iconButton(),
                EditAction::make()->iconButton(),
                DeleteAction::make()
                    ->iconButton()
                    ->disabled(fn (Airport $record): bool => AirportResource::isInUse($record))
                    ->tooltip(fn (Airport $record): ?string => AirportResource::isInUse($record)
                        ? __("Can't delete: linked to an event or flight")
                        : null),
            ])
            ->toolbarActions([
                BulkAction::make('deleteUnused')
                    ->label(__('Delete selected'))
                    ->icon(Heroicon::OutlinedTrash)
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalDescription(__('Airports that are still linked to an event or flight are skipped.'))
                    ->action(function (Collection $records): void {
                        /** @var Collection<int, Airport> $records */
                        [$inUse, $unused] = $records->partition(fn (Airport $airport): bool => AirportResource::isInUse($airport));
                        $unused->each->delete();

                        Notification::make()
                            ->title(__(':count airport(s) deleted', ['count' => $unused->count()]))
                            ->body($inUse->isNotEmpty() ? __('Skipped (in use): :list', ['list' => $inUse->pluck('icao')->join(', ')]) : null)
                            ->success()
                            ->send();
                    })
                    ->deselectRecordsAfterCompletion(),
            ]);
    }
}

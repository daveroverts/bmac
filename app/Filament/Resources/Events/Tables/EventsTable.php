<?php

namespace App\Filament\Resources\Events\Tables;

use App\Enums\BookingStatus;
use App\Filament\Resources\Events\Actions\EventActions;
use App\Models\Event;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class EventsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query
                ->with('type')
                ->withCount([
                    'bookings',
                    'bookings as booked_count' => fn (Builder $query) => $query->where('status', BookingStatus::BOOKED),
                ]))
            ->defaultSort('startEvent', 'desc')
            ->recordUrl(fn (Event $record): string => \App\Filament\Resources\Events\EventResource::getUrl('view', ['record' => $record]))
            ->recordClasses(fn (Event $record): ?string => $record->endEvent < now() ? 'opacity-60' : null)
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('name')
                    ->label(__('Name'))
                    ->searchable()
                    ->sortable()
                    ->weight('medium')
                    ->wrap(),
                TextColumn::make('type.name')
                    ->label(__('Type'))
                    ->badge()
                    ->color('gray'),
                TextColumn::make('startEvent')
                    ->label(__('Date'))
                    ->date('D j M Y')
                    ->sortable(),
                TextColumn::make('time')
                    ->label(__('Time'))
                    ->state(fn (Event $record): string => sprintf('%sz - %sz', $record->startEvent->format('Hi'), $record->endEvent->format('Hi'))),
                TextColumn::make('booked_count')
                    ->label(__('Booked'))
                    ->state(fn (Event $record): string => sprintf('%d / %d', $record->booked_count, $record->bookings_count))
                    ->alignCenter(),
                IconColumn::make('is_online')
                    ->label(__('Online'))
                    ->boolean()
                    ->alignCenter(),
            ])
            ->filters([
                SelectFilter::make('period')
                    ->label(__('Period'))
                    ->options(['upcoming' => __('Upcoming'), 'past' => __('Past')])
                    ->query(fn (Builder $query, array $data) => match ($data['value'] ?? null) {
                        'upcoming' => $query->where('endEvent', '>', now()),
                        'past' => $query->where('endEvent', '<=', now()),
                        default => $query,
                    }),
                SelectFilter::make('event_type_id')
                    ->label(__('Type'))
                    ->relationship('type', 'name'),
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make(),
                    ...array_filter(EventActions::all(), fn ($action): bool => ! $action instanceof ActionGroup),
                    DeleteAction::make()
                        ->visible(fn (Event $record): bool => $record->startEvent > now()),
                ]),
            ]);
    }
}

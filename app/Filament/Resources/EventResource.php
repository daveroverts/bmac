<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EventResource\Pages;
use App\Filament\Resources\EventResource\RelationManagers;
use App\Filament\Resources\EventResource\Traits\EventForm;
use App\Models\Event;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Filament\Tables\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class EventResource extends Resource
{
    use EventForm;

    protected static ?string $model = Event::class;

    protected static ?string $navigationIcon = 'heroicon-o-collection';

    protected static ?string $recordTitleAttribute = 'name';

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            'Type' => $record->type->name,
            'Date' => $record->startEvent->format('M j, Y')
        ];
    }

    protected static function getGlobalSearchEloquentQuery(): Builder
    {
        return parent::getGlobalSearchEloquentQuery()
            ->orderBy('startEvent')
            ->with(['type']);
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
                Filter::make('active')->query(fn (Builder $query): Builder => $query->where('endEvent', '>', now()))->default(),
                Filter::make('expired')->query(fn (Builder $query): Builder => $query->where('endEvent', '<', now())),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\BookingsRelationManager::class,
            RelationManagers\LinksRelationManager::class,
            RelationManagers\FaqsRelationManager::class,
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

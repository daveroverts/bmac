<?php

namespace App\Filament\Resources;

use App\Enums\EventType;
use App\Exports\BookingsExport;
use App\Filament\Resources\EventResource\Pages;
use App\Filament\Resources\EventResource\RelationManagers;
use App\Filament\Resources\EventResource\Traits\EventForm;
use App\Models\Event;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Filament\Tables\Actions\ButtonAction;
use Filament\Tables\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

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
                    ->dateTime('M j, Y Hi\z')->sortable(),
            ])->defaultSort('startEvent')
            ->filters([
                Filter::make('active')->query(fn (Builder $query): Builder => $query->where('endEvent', '>', now()))->default(),
                Filter::make('expired')->query(fn (Builder $query): Builder => $query->where('endEvent', '<', now())),
            ])
            ->prependActions([
                ButtonAction::make('import-bookings')
                    ->url(fn (Event $record): string => route('filament.resources.events.import-bookings', $record))
                    ->icon('heroicon-o-upload')
                    ->visible(fn (Event $record): bool => auth()->user()->can('update', $record)),
                ButtonAction::make('assign-routes')
                    ->url(fn (Event $record): string => route('filament.resources.events.assign-routes', $record))
                    ->icon('heroicon-o-upload')
                    ->visible(fn (Event $record): bool => auth()->user()->can('update', $record) && $record->event_type_id == EventType::MULTIFLIGHTS()->value),
                ButtonAction::make('send-email')
                    ->url(fn (Event $record): string => route('filament.resources.events.send-email', $record))
                    ->icon('heroicon-o-mail'),
                ButtonAction::make('export')
                    ->action(function (Event $record): BinaryFileResponse {
                        activity()
                            ->by(auth()->user())
                            ->on($record)
                            ->log('Export triggered');

                        return (new BookingsExport($record, false))->download('bookings.csv');
                    })
                    ->icon('heroicon-o-download')
                    ->modalButton('Start export')
                    ->requiresConfirmation()
                    ->hidden(fn (Event $record): bool => $record->event_type_id == EventType::MULTIFLIGHTS()->value),
                ButtonAction::make('export_multiflights')
                    ->label('Export')
                    ->action(function (Event $record, array $data): BinaryFileResponse {
                        activity()
                            ->by(auth()->user())
                            ->on($record)
                            ->log('Export triggered');

                        return (new BookingsExport($record, $data['with_emails']))->download('bookings.csv');
                    })
                    ->icon('heroicon-o-download')
                    ->modalButton('Start export')
                    ->form([
                        Toggle::make('with_emails')
                            ->default(false)
                    ])
                    ->visible(fn (Event $record): bool => $record->event_type_id == EventType::MULTIFLIGHTS()->value),
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
            'view' => Pages\ViewEvent::route('/{record}'),
            'edit' => Pages\EditEvent::route('/{record}/edit'),
            'import-bookings' => Pages\ImportBookings::route('{record}/import-bookings'),
            'assign-routes' => Pages\AssignRoutes::route('{record}/assign-routes'),
            'send-email' => Pages\SendEmail::route('{record}/send-email')
        ];
    }
}

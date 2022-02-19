<?php

namespace App\Filament\Resources\FaqResource\RelationManagers;

use App\Filament\Resources\EventResource\Traits\EventForm;
use Filament\Tables;
use Filament\Resources\Table;
use Filament\Resources\RelationManagers\BelongsToManyRelationManager;

class EventsRelationManager extends BelongsToManyRelationManager
{
    use EventForm;

    protected static string $relationship = 'events';

    protected static ?string $recordTitleAttribute = 'id';

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nameDate')->label('Name')
            ])
            ->filters([
                //
            ]);
    }
}

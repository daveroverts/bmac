<?php

namespace App\Filament\Resources\Events\RelationManagers;

use Filament\Actions\AttachAction;
use Filament\Actions\DetachAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class FaqsRelationManager extends RelationManager
{
    protected static string $relationship = 'faqs';

    protected static ?string $title = 'FAQ';

    public function isReadOnly(): bool
    {
        return false;
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('question')
            ->columns([
                TextColumn::make('question')->label(__('Question'))->wrap(),
                IconColumn::make('is_online')->label(__('Online'))->boolean(),
            ])
            ->headerActions([
                AttachAction::make()
                    ->label(__('Attach FAQ'))
                    ->preloadRecordSelect()
                    ->recordSelectSearchColumns(['question']),
            ])
            ->recordActions([
                DetachAction::make()->iconButton(),
            ]);
    }
}

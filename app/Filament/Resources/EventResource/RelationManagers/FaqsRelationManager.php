<?php

namespace App\Filament\Resources\EventResource\RelationManagers;

use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\RelationManagers\BelongsToManyRelationManager;
use Filament\Resources\Table;
use Filament\Tables;

class FaqsRelationManager extends BelongsToManyRelationManager
{
    protected static string $relationship = 'faqs';

    protected static ?string $recordTitleAttribute = 'id';

    public static function form(Form $form): Form
    {
        return $form
            ->columns(1)
            ->schema([
                Forms\Components\Toggle::make('is_online')
                    ->label('Show online?')
                    ->default(true)
                    ->required(),
                Forms\Components\TextInput::make('question')
                    ->required()
                    ->maxLength(255),
                Forms\Components\RichEditor::make('answer')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('question'),
                Tables\Columns\BooleanColumn::make('is_online')->label('Show online?'),
            ])
            ->filters([
                //
            ]);
    }
}

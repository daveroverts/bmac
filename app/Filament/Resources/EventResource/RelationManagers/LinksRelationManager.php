<?php

namespace App\Filament\Resources\EventResource\RelationManagers;

use Filament\Forms;
use Filament\Tables;
use Filament\Resources\Form;
use Filament\Resources\Table;
use App\Models\AirportLinkType;
use Filament\Resources\RelationManagers\HasManyRelationManager;

class LinksRelationManager extends HasManyRelationManager
{
    protected static string $relationship = 'links';

    protected static ?string $recordTitleAttribute = 'id';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('event_link_type_id')
                    ->label('Type')
                    ->required()
                    ->options(AirportLinkType::all(['id', 'name'])->pluck('name', 'id')),
                Forms\Components\TextInput::make('name')
                    ->minLength(5)
                    ->maxLength(255),
                Forms\Components\TextInput::make('url')
                    ->required()
                    ->url()
                    ->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('type.name')->sortable(),
                Tables\Columns\TextColumn::make('name')->sortable(),
                Tables\Columns\TextColumn::make('url')->sortable(),
            ])
            ->filters([
                //
            ]);
    }
}

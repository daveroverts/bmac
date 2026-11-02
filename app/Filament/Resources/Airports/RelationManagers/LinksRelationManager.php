<?php

namespace App\Filament\Resources\Airports\RelationManagers;

use App\Models\AirportLink;
use App\Models\AirportLinkType;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class LinksRelationManager extends RelationManager
{
    protected static string $relationship = 'links';

    protected static ?string $title = 'Links';

    public function isReadOnly(): bool
    {
        return false;
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('airportLinkType_id')
                    ->label(__('Type'))
                    ->options(fn () => AirportLinkType::forDropdown())
                    ->required(),
                TextInput::make('name')
                    ->label(__('Name'))
                    ->helperText(__('Leave empty to use the type as name')),
                TextInput::make('url')
                    ->label(__('URL'))
                    ->url()
                    ->required()
                    ->placeholder('https://example.org')
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with('type'))
            ->columns([
                TextColumn::make('type.name')->label(__('Type'))->badge(),
                TextColumn::make('name')
                    ->label(__('Name'))
                    ->state(fn (AirportLink $record): string => $record->name ?? $record->type->name),
                TextColumn::make('url')
                    ->label(__('URL'))
                    ->url(fn (AirportLink $record): string => $record->url, shouldOpenInNewTab: true)
                    ->limit(50),
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->recordActions([
                EditAction::make()->iconButton(),
                DeleteAction::make()->iconButton(),
            ]);
    }
}

<?php

namespace App\Filament\Resources\FaqResource\RelationManagers;

use Filament\Tables;
use Filament\Resources\Table;
use Illuminate\Database\Eloquent\Model;
use App\Filament\Resources\EventResource\Traits\EventForm;
use App\Models\Event;
use Filament\Forms\Components\Select;
use Filament\Resources\Form;
use Filament\Resources\RelationManagers\BelongsToManyRelationManager;

class EventsRelationManager extends BelongsToManyRelationManager
{
    use EventForm;

    protected static string $relationship = 'events';

    protected static ?string $recordTitleAttribute = 'name';

    public static function attachForm(Form $form): Form
    {
        return Form::make()->schema([
            Select::make('recordId')
                ->label('Event')
                ->options(Event::where('endEvent', '>', now())
                    ->orderBy('startEvent')->get()->mapWithKeys(fn (Event $event) => [$event->id => $event->name_date]))
        ]);
    }

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

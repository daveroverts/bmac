<?php

namespace App\Filament\Resources\Events\Pages;

use App\Filament\Resources\Events\Actions\EventActions;
use App\Filament\Resources\Events\EventResource;
use Filament\Actions\ActionGroup;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Icons\Heroicon;

class ViewEvent extends ViewRecord
{
    protected static string $resource = EventResource::class;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedInformationCircle;

    public static function getNavigationLabel(): string
    {
        return __('Details');
    }

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
            EventActions::export(),
            ActionGroup::make([
                EventActions::addTimeslots(),
                EventActions::importBookings(),
                EventActions::autoAssign(),
                EventActions::routeAssign(),
                EventActions::sendBulkEmail(),
                EventActions::sendFinalInformation(),
                EventActions::deleteAllBookings(),
            ])
                ->label(__('More actions'))
                ->icon(Heroicon::OutlinedEllipsisVertical)
                ->button()
                ->color('gray'),
        ];
    }
}

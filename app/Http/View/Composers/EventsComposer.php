<?php

namespace App\Http\View\Composers;

use App\Models\Event;
use Illuminate\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class EventsComposer
{
    protected Collection $events;

    public function __construct()
    {
        $this->events = Event::where('endEvent', '>', now())
            ->orderBy('startEvent')
            ->where('is_online', true)
            ->where(function (Builder $query): void {
                $query->where('show_on_homepage', true)
                    ->when(auth()->id(), fn (Builder $query, $userId) => $query->orWhereHas('bookings', function (Builder $query) use ($userId): void {
                        $query->where('user_id', $userId);
                    }));
            })->get();
    }

    public function compose(View $view): void
    {
        $view->with('navbarEvents', $this->events);
    }
}

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
            ->where(function ($query) {
                /** @var Builder $query */
                $query->where('show_on_homepage', true)
                    ->when(auth()->id(), function ($query, $userId) {
                        return $query->orWhereHas('bookings', function ($query) use ($userId) {
                            /** @var Builder $query */
                            $query->where('user_id', $userId);
                        });
                    });
            })->get();
    }

    public function compose(View $view): void
    {
        $view->with('navbarEvents', $this->events);
    }
}

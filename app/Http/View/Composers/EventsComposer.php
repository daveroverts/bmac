<?php

namespace App\Http\View\Composers;

use App\Models\Event;
use Illuminate\View\View;
use Illuminate\Database\Eloquent\Builder;

class EventsComposer
{
    /**
     *
     * @var Event $events
     */
    protected $events;

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

    public function compose(View $view)
    {
        $view->with('navbarEvents', $this->events);
    }
}

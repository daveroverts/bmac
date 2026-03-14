<?php

namespace App\Http\View\Composers;

use App\Models\Booking;
use App\Models\Event;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class EventsComposer
{
    protected Collection $events;

    /** @var Collection<int, Collection<int, Booking>> */
    protected Collection $userBookingsByEvent;

    public function __construct()
    {
        $userId = auth()->id();

        $this->events = Event::where('endEvent', '>', now())
            ->orderBy('startEvent')
            ->where('is_online', true)
            ->where(function (Builder $query) use ($userId): void {
                $query->where('show_on_homepage', true)
                    ->when($userId, fn (Builder $query, $id) => $query->orWhereHas('bookings', function (Builder $query) use ($id): void {
                        $query->where('user_id', $id);
                    }));
            })->get();

        $this->userBookingsByEvent = $userId
            ? Booking::where('user_id', $userId)
                ->whereIn('event_id', $this->events->pluck('id'))
                ->get()
                ->groupBy('event_id')
            : collect();
    }

    public function compose(View $view): void
    {
        $view->with('navbarEvents', $this->events);
        $view->with('navbarUserBookings', $this->userBookingsByEvent);
    }
}

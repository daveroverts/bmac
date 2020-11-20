<?php

namespace App\Http\Livewire;

use App\Models\Event;
use Livewire\Component;
use App\Enums\EventType;
use App\Enums\BookingStatus;
use Illuminate\Database\Eloquent\Builder;

class Bookings extends Component
{

    public Event $event;
    public $bookings;
    public $filter = null;
    public $total = 0;
    public $booked = 0;

    public function filter($filter)
    {
        $this->filter = strtolower($filter);
    }

    public function render()
    {
        // @TODO Check should actually be in a policy
        $filter = $this->filter;
        if ($this->event->is_online || auth()->check() && auth()->user()->isAdmin) {
            $this->bookings = $this->event->bookings()
                ->with([
                    'event',
                    'user',
                    'flights' => function ($query) use ($filter) {
                        switch ($filter) {
                            case 'departures':
                                $query->where('dep', $this->event->dep)
                                    ->orderBy('ctot');
                                break;
                            case 'arrivals':
                                $query->where('arr', $this->event->arr)
                                    ->orderBy('eta');
                                break;
                            default:
                                $query->orderBy('eta')
                                    ->orderBy('ctot');
                        }
                    },
                    'flights.airportDep',
                    'flights.airportArr',
                ])
                ->withCount('flights')
                ->get();
        } else {
            abort_unless(auth()->check() && auth()->user()->isAdmin, 404);
        }

        $this->booked = $this->bookings->where('status', BookingStatus::BOOKED)
            ->filter(function ($booking) {
                /** @var Booking $booking */
                return $booking->flights_count;
            })->count();

        if ($this->event->event_type_id == EventType::MULTIFLIGHTS) {
            $this->total = $this->bookings->count();
        } else {
            $this->total = $this->bookings->sum(function ($booking) {
                /** @var Booking $booking */
                return $booking->flights_count;
            });
        }

        return view('livewire.bookings');
    }
}

<?php

namespace App\Http\Livewire;

use App\Models\Event;
use App\Models\Booking;
use Livewire\Component;
use App\Enums\EventType;
use App\Enums\BookingStatus;

class Bookings extends Component
{
    /** @var Event */
    public Event $event;

    /** @var int */
    public $refreshInSeconds = 0;

    /** @var Booking */
    public $bookings;

    /** @var string|null */
    public $filter = null;

    /** @var int */
    public $total = 0;

    /** @var int */
    public $booked = 0;

    public function filter($filter)
    {
        $this->filter = strtolower($filter);
    }

    public function mount()
    {
        // Only enable polling if event is 'active'
        if (now()->between($this->event->startBooking, $this->event->endEvent)) {
            $this->refreshInSeconds = 15;
        }
    }

    public function render()
    {
        $filter = $this->filter;
        // @TODO Check should actually be in a policy
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

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
        if ($this->event->is_online || auth()->check() && auth()->user()->isAdmin) {
            if ($this->event->hasOrderButtons()) {
                switch (strtolower($this->filter)) {
                    case 'departures':
                        $this->bookings = $this->event->bookings()
                            ->with([
                                'event',
                                'user',
                                'flights' => function ($query) {
                                    $query->where('dep', $this->event->dep);
                                    $query->orderBy('ctot');
                                },
                                'flights.airportDep',
                                'flights.airportArr',
                            ])
                            ->withCount(['flights' => function (Builder $query) {
                                $query->where('dep', $this->event->dep);
                            },])
                            ->get();
                        break;
                    case 'arrivals':
                        $this->bookings = $this->event->bookings()
                            ->with([
                                'event',
                                'user',
                                'flights' => function ($query) {
                                    $query->where('arr', $this->event->arr);
                                    $query->orderBy('eta');
                                },
                                'flights.airportDep',
                                'flights.airportArr',
                            ])
                            ->withCount(['flights' => function (Builder $query) {
                                $query->where('arr', $this->event->arr);
                            },])
                            ->get();
                        break;
                    default:
                        $this->bookings = $this->event->bookings()
                            ->with([
                                'event',
                                'user',
                                'flights' => function ($query) {
                                    $query->orderBy('eta');
                                    $query->orderBy('ctot');
                                },
                                'flights.airportDep',
                                'flights.airportArr',
                            ])
                            ->withCount('flights')
                            ->get();
                }
            } else {
                $this->bookings = $this->event->bookings()
                    ->with([
                        'event',
                        'user',
                        'flights' => function ($query) {
                            $query->orderBy('eta');
                            $query->orderBy('ctot');
                        },
                        'flights.airportDep',
                        'flights.airportArr',
                    ])
                    ->withCount('flights')
                    ->get();
            }
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

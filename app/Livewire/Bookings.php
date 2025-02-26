<?php

namespace App\Livewire;

use App\Models\Event;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Livewire\Attributes\Computed;
use Livewire\Component;
use App\Enums\BookingStatus;

/**
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Booking[] $bookings
 */
class Bookings extends Component
{
    public Event $event;

    public int $refreshInSeconds = 0;

    public ?string $filter = null;

    public int $total = 0;

    public int $booked = 0;

    public function setFilter($filter): void
    {
        $this->filter = strtolower((string) $filter);

        unset($this->bookings);
    }

    public function mount(): void
    {
        // Only enable polling if event is 'active'
        if (now()->between($this->event->startBooking, $this->event->endEvent)) {
            $this->refreshInSeconds = 15;
        }
    }

    public function render()
    {
        abort_unless(auth()->user()?->isAdmin || $this->event->is_online, 404);

        $this->booked = $this->bookings->where('status', BookingStatus::BOOKED)->count();

        $this->total = $this->bookings->count();

        // https://github.com/TomasVotruba/bladestan/issues/65#issuecomment-1582383622
        return view('livewire.bookings', [
            'event' => $this->event,
            'refreshInSeconds' => $this->refreshInSeconds,
            'filter' => $this->filter,
            'total' => $this->total,
            'booked' => $this->booked,
        ]);
    }

    #[Computed]
    public function bookings()
    {
        return $this->event->bookings()
            ->with([
                'event',
                'user',
//                'flights' => function ($query) {
//                    switch ($this->filter) {
//                        case 'departures':
//                            $query->where('dep', $this->event->dep)
//                                ->orderBy('ctot');
//                            break;
//                        case 'arrivals':
//                            $query->where('arr', $this->event->arr)
//                                ->orderBy('eta');
//                            break;
//                        default:
//                            $query->orderBy('eta')
//                                ->orderBy('ctot');
//                    }
//                },
                'flights.airportDep',
                'flights.airportArr',
            ])
            ->withWhereHas('flights', function (Builder|HasMany $query): void {
                match ($this->filter) {
                    'departures' => $query->where('dep', $this->event->dep)->orderBy('ctot'),
                    'arrivals' => $query->where('arr', $this->event->arr)->orderBy('eta'),
                    default => $query->orderBy('eta')->orderBy('ctot'),
                };
            })
            ->get()
            ->sortBy(function ($booking) {
                switch ($this->filter) {
                    case 'departures':
                        return $booking->flights->first()->ctot?->timestamp;
                    case 'arrivals':
                        return $booking->flights->first()->eta?->timestamp;
                    default:
                        $flight = $booking->flights->first();
                        // Default needs to order by eta, if not filled in, by ctot
                        return $flight->eta?->timestamp ?: $flight->ctot?->timestamp;
                }
            });
    }
}

<?php

namespace App\Actions\Booking;

use App\Models\Booking;
use App\Models\Event;
use Illuminate\Support\Facades\DB;

class AutoAssignFlightsAction
{
    /**
     * Alternate two oceanic tracks/routes over the event's flights (ordered by CTOT) and spread their entry levels
     * between the minimum and maximum FL: odd flights count down from the maximum, even flights up from the minimum.
     *
     * @param  array{oceanicTrack1: string, route1: string, oceanicTrack2: string, route2: string, minFL: int|string, maxFL: int|string, checkAssignAllFlights?: mixed}  $data
     * @return int The number of bookings that were assigned.
     */
    public function handle(Event $event, array $data): int
    {
        $minFL = (int) $data['minFL'];
        $maxFL = (int) $data['maxFL'];

        $bookings = $event->bookings()
            ->with(['flights' => function ($query): void {
                $query->orderBy('ctot');
            }])
            ->unless(! empty($data['checkAssignAllFlights']), function ($query): void {
                $query->booked();
            })
            ->get();

        $count = 0;
        $flOdd = $maxFL;
        $flEven = $minFL;

        DB::transaction(function () use ($bookings, $data, $minFL, $maxFL, &$count, &$flOdd, &$flEven): void {
            /** @var Booking $booking */
            foreach ($bookings as $booking) {
                $flight = $booking->flights->first();
                $count++;
                if ($count % 2 === 0) {
                    $flight->fill([
                        'oceanicTrack' => $data['oceanicTrack2'],
                        'route' => $data['route2'],
                        'oceanicFL' => $flEven,
                    ]);
                    $flEven += 10;
                    if ($flEven > $maxFL) {
                        $flEven = $minFL;
                    }
                } else {
                    $flight->fill([
                        'oceanicTrack' => $data['oceanicTrack1'],
                        'route' => $data['route1'],
                        'oceanicFL' => $flOdd,
                    ]);
                    $flOdd -= 10;
                    if ($flOdd < $minFL) {
                        $flOdd = $maxFL;
                    }
                }

                $flight->save();
            }
        });

        activity()
            ->by(auth()->user())
            ->on($event)
            ->withProperties([
                'Track 1' => $data['oceanicTrack1'],
                'Route 1' => $data['route1'],
                'Track 2' => $data['oceanicTrack2'],
                'Route 2' => $data['route2'],
                'count' => $count,
            ])
            ->log('Flights auto-assigned');

        return $count;
    }
}

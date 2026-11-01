<?php

namespace App\Console\Commands;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\Event;
use Illuminate\Console\Command;

#[\Illuminate\Console\Attributes\Description('Clean up reservations that have exceeded the timeout. If no event is provided, it is done for all active events.')]
#[\Illuminate\Console\Attributes\Signature('event:cleanup-reservations {eventId?}')]
class EventCleanupReservationsCommand extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $eventId = $this->argument('eventId');
        if ($eventId) {
            $event = Event::find($eventId);
            if (!$event) {
                $this->error('Could not find event with id ' . $eventId);
                return Command::FAILURE;
            }

            $this->cleanupReservations($event);
        } else {
            $this->withProgressBar(Event::query()->upcoming()->online()->get(), function (Event $event): void {
                $this->cleanupReservations($event);
            });
        }

        return Command::SUCCESS;
    }

    private function cleanupReservations(Event $event): void
    {
        $event->bookings()
            ->reserved()
            ->where('updated_at', '<=', now()->subMinutes(Booking::RESERVATION_TIMEOUT_MINUTES))
            ->chunkById(100, function (\Illuminate\Database\Eloquent\Collection $bookings): void {
                /** @var Booking $booking */
                foreach ($bookings as $booking) {
                    $booking->status = BookingStatus::UNASSIGNED;
                    $booking->user_id = null;
                    $booking->save();
                }
            });
    }
}

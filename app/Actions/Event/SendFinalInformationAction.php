<?php

namespace App\Actions\Event;

use App\Events\EventFinalInformation;
use App\Models\Event;

class SendFinalInformationAction
{
    /**
     * Send the final information e-mail to every booked pilot of the event.
     *
     * @param  bool  $force  Also send to pilots who already received it.
     * @return array{sent: int, skipped: int}
     */
    public function handle(Event $event, bool $force = false): array
    {
        $bookings = $event->bookings()
            ->with(['user', 'flights'])
            ->booked()
            ->get();

        $sent = 0;
        $skipped = 0;

        foreach ($bookings as $booking) {
            if (! $booking->has_received_final_information_email || $force) {
                event(new EventFinalInformation($booking));
                $sent++;
            } else {
                $skipped++;
            }
        }

        activity()
            ->by(auth()->user())
            ->on($event)
            ->withProperty('count', $sent)
            ->log('Final Information E-mail');

        return ['sent' => $sent, 'skipped' => $skipped];
    }
}

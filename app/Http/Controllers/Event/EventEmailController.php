<?php

namespace App\Http\Controllers\Event;

use App\Enums\BookingStatus;
use App\Events\EventBulkEmail;
use App\Events\EventFinalInformation;
use App\Http\Controllers\Controller;
use App\Http\Requests\Event\Admin\SendEmail;
use App\Models\Event;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EventEmailController extends Controller
{
    public function createBulk(Event $event): View
    {
        return view('event.admin.sendEmail', ['event' => $event]);
    }

    public function sendBulk(SendEmail $request, Event $event): JsonResponse|RedirectResponse
    {
        if ($request->testmode) {
            event(new EventBulkEmail($event, $request->all(), collect([auth()->user()])));

            return response()->json(['success' => __('Email has been sent to yourself')]);
        }

        /** @var \Illuminate\Support\Collection<int, User> $users */
        $users = User::whereHas('bookings', function (Builder $query) use ($event): void {
            $query->where('event_id', $event->id);
            $query->where('status', BookingStatus::BOOKED->value);
        })->get();
        event(new EventBulkEmail($event, $request->all(), $users));
        flashMessage('success', __('Done'), __('Bulk E-mail has been sent to :count people!', ['count' => $users->count()]));

        return to_route('admin.events.index');
    }

    public function sendFinal(Request $request, Event $event): RedirectResponse|JsonResponse
    {
        $bookings = $event->bookings()
            ->with(['user', 'flights'])
            ->where('status', BookingStatus::BOOKED->value)
            ->get();

        if ($request->testmode) {
            /** @var \App\Models\Booking $randomBooking */
            $randomBooking = $bookings->random();
            event(new EventFinalInformation($randomBooking, $request->user()));

            return response()->json(['success' => __('Email has been sent to yourself')]);
        }

        $count = $bookings->count();
        $countSkipped = 0;
        /** @var \App\Models\Booking $booking */
        foreach ($bookings as $booking) {
            if (!$booking->has_received_final_information_email || $request->forceSend) {
                event(new EventFinalInformation($booking));
            } else {
                $count--;
                $countSkipped++;
            }
        }

        $message = __('Final Information has been sent to :count people!', ['count' => $count]);
        if ($countSkipped !== 0) {
            $message .= ' ' . __('However, :count were skipped, because they already received one', ['count' => $countSkipped]);
        }

        flashMessage('success', __('Done'), $message);
        activity()
            ->by(auth()->user())
            ->on($event)
            ->withProperty('count', $count)
            ->log('Final Information E-mail');

        return to_route('admin.events.index');
    }
}

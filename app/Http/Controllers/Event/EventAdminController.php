<?php

namespace App\Http\Controllers\Event;

use App\Models\User;
use App\Models\Event;
use Illuminate\View\View;
use App\Enums\BookingStatus;
use Illuminate\Http\Request;
use App\Policies\EventPolicy;
use App\Events\EventBulkEmail;
use Illuminate\Http\JsonResponse;
use App\Events\EventFinalInformation;
use Illuminate\Http\RedirectResponse;
use App\Http\Controllers\AdminController;
use Illuminate\Database\Eloquent\Builder;
use App\Http\Requests\Event\Admin\SendEmail;

class EventAdminController extends AdminController
{
    public function __construct()
    {
        $this->authorizeResource(EventPolicy::class, 'event');
    }

    public function sendEmailForm(Event $event): View
    {
        return view('event.admin.sendEmail', compact('event'));
    }

    public function sendEmail(SendEmail $request, Event $event): JsonResponse|RedirectResponse
    {
        if ($request->testmode) {
            event(new EventBulkEmail($event, $request->all(), collect([auth()->user()])));
            return response()->json(['success' => __('Email has been sent to yourself')]);
        } else {
            /* @var User $users */
            $users = User::whereHas('bookings', function (Builder $query) use ($event) {
                $query->where('event_id', $event->id);
                $query->where('status', BookingStatus::BOOKED);
            })->get();
            event(new EventBulkEmail($event, $request->all(), $users));
            flashMessage('success', __('Done'), __('Bulk E-mail has been sent to :count people!', ['count' => $users->count()]));
            return to_route('admin.events.index');
        }
    }

    public function sendFinalInformationMail(Request $request, Event $event): RedirectResponse|JsonResponse
    {
        $bookings = $event->bookings()
            ->with(['user', 'flights'])
            ->where('status', BookingStatus::BOOKED)
            ->get();

        if ($request->testmode) {
            event(new EventFinalInformation($bookings->random(), $request->user()));

            return response()->json(['success' => __('Email has been sent to yourself')]);
        } else {
            $count = $bookings->count();
            $countSkipped = 0;
            foreach ($bookings as $booking) {
                if (!$booking->has_received_final_information_email || $request->forceSend) {
                    event(new EventFinalInformation($booking));
                } else {
                    $count--;
                    $countSkipped++;
                }
            }
            $message = __('Final Information has been sent to :count people!', ['count' => $count]);
            if ($countSkipped != 0) {
                $message .= ' ' . __('However, :count where skipped, because they already received one', ['count' => $count]);
            }
            flashMessage('success', __('Done'), $message);
            activity()
                ->by(auth()->user())
                ->on($event)
                ->withProperty('count', $count)
                ->log('Final Information E-mail');
        }

        return to_route('admin.events.index');
    }
}

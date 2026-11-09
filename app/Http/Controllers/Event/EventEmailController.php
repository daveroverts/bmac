<?php

namespace App\Http\Controllers\Event;

use App\Actions\Event\SendFinalInformationAction;
use App\Events\EventBulkEmail;
use App\Events\EventFinalInformation;
use App\Http\Controllers\Controller;
use App\Http\Requests\Event\Admin\SendEmail;
use App\Http\Requests\Event\Admin\SendFinalInformation;
use App\Models\Event;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
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
            event(new EventBulkEmail($event, $request->validated(), User::query()->whereKey(auth()->id())->get()));

            return response()->json(['success' => __('Email has been sent to yourself')]);
        }

        /** @var \Illuminate\Support\Collection<int, User> $users */
        $users = User::whereHas('bookings', function (Builder $query) use ($event): void {
            $query->where('event_id', $event->id)
                ->booked();
        })->get();
        event(new EventBulkEmail($event, $request->validated(), $users));
        flashMessage('success', __('Done'), __('Bulk E-mail has been sent to :count people!', ['count' => $users->count()]));

        return to_route('admin.events.index');
    }

    public function sendFinal(SendFinalInformation $request, Event $event, SendFinalInformationAction $action): RedirectResponse|JsonResponse
    {
        $bookings = $event->bookings()
            ->with(['user', 'flights'])
            ->booked()
            ->get();

        if ($request->testmode) {
            if ($bookings->isEmpty()) {
                return response()->json(['error' => __('No booked bookings found for this event')], 422);
            }

            /** @var \App\Models\Booking $randomBooking */
            $randomBooking = $bookings->random();
            event(new EventFinalInformation($randomBooking, $request->user()));

            return response()->json(['success' => __('Email has been sent to yourself')]);
        }

        ['sent' => $count, 'skipped' => $countSkipped] = $action->handle($event, (bool) $request->forceSend);

        $message = __('Final Information has been sent to :count people!', ['count' => $count]);
        if ($countSkipped !== 0) {
            $message .= ' ' . __('However, :count were skipped, because they already received one', ['count' => $countSkipped]);
        }

        flashMessage('success', __('Done'), $message);

        return to_route('admin.events.index');
    }
}

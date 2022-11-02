<?php

namespace App\Http\Controllers\Event;

use App\Models\User;
use App\Models\Event;
use App\Models\Airport;
use App\Models\EventType;
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
use App\Http\Requests\Event\Admin\StoreEvent;
use App\Http\Requests\Event\Admin\UpdateEvent;

class EventAdminController extends AdminController
{
    public function __construct()
    {
        $this->authorizeResource(EventPolicy::class, 'event');
    }

    public function index(): View
    {
        $events = Event::orderByDesc('startEvent')
            ->with('type')
            ->paginate();
        return view('event.admin.overview', compact('events'));
    }

    public function create(): View
    {
        $event = new Event();
        $airports = Airport::all(['id', 'icao', 'iata', 'name'])->keyBy('id')
            ->map(function ($airport) {
                /** @var Airport $airport */
                return "$airport->icao | $airport->name | $airport->iata";
            });
        $eventTypes = EventType::all()->pluck('name', 'id');
        return view('event.admin.form', compact('event', 'airports', 'eventTypes'));
    }

    public function store(StoreEvent $request): RedirectResponse
    {
        $event = new Event();
        $event->fill($request->only(
            'is_online',
            'show_on_homepage',
            'name',
            'event_type_id',
            'import_only',
            'uses_times',
            'multiple_bookings_allowed',
            'is_oceanic_event',
            'dep',
            'arr',
            'image_url',
            'description'
        ));
        $event->fill([
            'startEvent' => $request->date('startEvent'),
            'endEvent' => $request->date('endEvent'),
            'startBooking' => $request->date('startBooking'),
            'endBooking' => $request->date('endBooking'),
        ])->save();

        flashMessage('success', __('Done'), __('Event has been created!'));
        return to_route('admin.events.index');
    }

    public function show(Event $event): View
    {
        return view('event.admin.show', compact('event'));
    }

    public function edit(Event $event): View
    {
        $airports = Airport::all(['id', 'icao', 'iata', 'name'])->keyBy('id')
            ->map(function ($airport) {
                /** @var Airport $airport */
                return "$airport->icao | $airport->name | $airport->iata";
            });
        $eventTypes = EventType::all()->pluck('name', 'id');
        return view('event.admin.form', compact('event', 'airports', 'eventTypes'));
    }

    public function update(UpdateEvent $request, Event $event): RedirectResponse
    {
        $event->fill($request->only(
            'is_online',
            'show_on_homepage',
            'name',
            'event_type_id',
            'import_only',
            'uses_times',
            'multiple_bookings_allowed',
            'is_oceanic_event',
            'dep',
            'arr',
            'image_url',
            'description'
        ));
        $event->fill([
            'startEvent' => $request->date('startEvent'),
            'endEvent' => $request->date('endEvent'),
            'startBooking' => $request->date('startBooking'),
            'endBooking' => $request->date('endBooking'),
        ])->save();
        flashMessage('success', __('Done'), __('Event has been updated!'));
        return to_route('admin.events.index');
    }

    public function destroy(Event $event): RedirectResponse
    {
        if ($event->startEvent > now()) {
            $event->delete();
            flashMessage('success', __('Done'), __(':event has been deleted!', ['event' => $event->name]));
            return redirect()->back();
        } else {
            flashMessage('danger', __('Danger'), __('Event can no longer be deleted!'));
            return redirect()->back();
        }
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

    public function deleteAllBookings(Request $request, Event $event): RedirectResponse
    {
        activity()
            ->by(auth()->user())
            ->on($event)
            ->log('Delete all bookings');

        if ($event->endEvent <= now()) {
            flashMessage('danger', __('Danger'), __('Booking can no longer be deleted'));
            return back();
        }

        $event->bookings()->delete();
        flashMessage('success', __('Bookings deleted'), __('All bookings have been deleted'));
        return to_route('admin.events.index');
    }
}

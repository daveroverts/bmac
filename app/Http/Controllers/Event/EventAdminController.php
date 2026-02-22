<?php

namespace App\Http\Controllers\Event;

use App\Models\Event;
use App\Models\Airport;
use App\Models\EventType;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use App\Http\Controllers\Controller;
use App\Policies\EventPolicy;
use App\Http\Requests\Event\Admin\StoreEvent;
use App\Http\Requests\Event\Admin\UpdateEvent;

class EventAdminController extends Controller
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
        return view('event.admin.overview', ['events' => $events]);
    }

    public function create(): View
    {
        $event = new Event();
        $airports = Airport::all(['id', 'icao', 'iata', 'name'])->keyBy('id')
            ->map(fn ($airport): string =>
                /** @var Airport $airport */
                sprintf('%s | %s | %s', $airport->icao, $airport->name, $airport->iata));
        $eventTypes = EventType::pluck('name', 'id');
        return view('event.admin.form', ['event' => $event, 'airports' => $airports, 'eventTypes' => $eventTypes]);
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
        return view('event.admin.show', ['event' => $event]);
    }

    public function edit(Event $event): View
    {
        $airports = Airport::all(['id', 'icao', 'iata', 'name'])->keyBy('id')
            ->map(fn ($airport): string =>
                /** @var Airport $airport */
                sprintf('%s | %s | %s', $airport->icao, $airport->name, $airport->iata));
        $eventTypes = EventType::pluck('name', 'id');
        return view('event.admin.form', ['event' => $event, 'airports' => $airports, 'eventTypes' => $eventTypes]);
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
            return back();
        }

        flashMessage('danger', __('Danger'), __('Event can no longer be deleted!'));
        return back();
    }
}

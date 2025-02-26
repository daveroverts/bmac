<?php

namespace App\Http\Controllers\EventLink;

use App\Models\Event;
use App\Models\EventLink;
use Illuminate\View\View;
use App\Models\AirportLinkType;
use App\Policies\EventLinkPolicy;
use Illuminate\Http\RedirectResponse;
use App\Http\Controllers\AdminController;
use App\Http\Requests\EventLink\Admin\StoreEventLink;
use App\Http\Requests\EventLink\Admin\UpdateEventLink;

class EventLinkAdminController extends AdminController
{
    public function __construct()
    {
        $this->authorizeResource(EventLinkPolicy::class, 'eventLink');
    }

    public function index(): View
    {
        $eventLinks = EventLink::orderBy('event_id', 'asc')
            ->with(['event', 'type'])
            ->paginate();
        return view('eventLink.admin.overview', compact('eventLinks'));
    }

    public function create(): View
    {
        $eventLink = new EventLink();
        $eventLinkTypes = AirportLinkType::pluck('name', 'id');
        $events = Event::where('endEvent', '>', now())
            ->orderBy('startEvent')
            ->get(['id', 'name', 'startEvent'])
            ->keyBy('id')
            ->map(fn ($event) =>
                /** @var Event $event */
                "$event->name [{$event->startEvent->format('d-m-Y')}]");

        return view('eventLink.admin.form', compact('eventLink', 'eventLinkTypes', 'events'));
    }

    public function store(StoreEventLink $request): RedirectResponse
    {
        $eventLink = EventLink::create($request->validated());
        flashMessage(
            'success',
            __('Done'),
            __(':Type item has been added for :event', ['Type' => $eventLink->type->name, 'event' => $eventLink->event->name])
        );
        return to_route('admin.eventLinks.index');
    }

    public function edit(EventLink $eventLink): View
    {
        $eventLinkTypes = AirportLinkType::pluck('name', 'id');
        return view('eventLink.admin.form', compact('eventLink', 'eventLinkTypes'));
    }

    public function update(UpdateEventLink $request, EventLink $eventLink): RedirectResponse
    {
        $eventLink->update($request->validated());
        flashMessage('success', __('Done'), __('Link has been updated'));
        return to_route('admin.eventLinks.index');
    }

    public function destroy(EventLink $eventLink): RedirectResponse
    {
        $eventLink->delete();
        flashMessage('success', __('Event link deleted'), __('Event link has been deleted'));
        return back();
    }
}

<?php

namespace App\Http\Controllers\EventLink;

use App\Http\Controllers\AdminController;
use App\Http\Requests\EventLink\Admin\StoreEventLink;
use App\Http\Requests\EventLink\Admin\UpdateEventLink;
use App\Models\AirportLinkType;
use App\Models\Event;
use App\Models\EventLink;
use App\Policies\EventLinkPolicy;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

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

    public function create(Event $event): View
    {
        $eventLink = new EventLink();
        $eventLinkTypes = AirportLinkType::all(['id', 'name'])->pluck('name', 'id');
        $events = Event::where('endEvent', '>', now())
            ->orderBy('startEvent')
            ->get(['id', 'name', 'startEvent'])
            ->keyBy('id')
            ->map(function ($event) {
                /** @var Event $event */
                return "$event->name [{$event->startEvent->format('d-m-Y')}]";
            });

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

        return redirect(route('admin.eventLinks.index'));
    }

    public function edit(EventLink $eventLink): View
    {
        $eventLinkTypes = AirportLinkType::all(['id', 'name'])->pluck('name', 'id');

        return view('eventLink.admin.form', compact('eventLink', 'eventLinkTypes'));
    }

    public function update(UpdateEventLink $request, EventLink $eventLink): RedirectResponse
    {
        $eventLink->update($request->validated());
        flashMessage('success', __('Done'), __('Link has been updated'));

        return redirect(route('admin.eventLinks.index'));
    }

    public function destroy(EventLink $eventLink): RedirectResponse
    {
        $eventLink->delete();
        flashMessage('success', __('Event link deleted'), __('Event link has been deleted'));

        return back();
    }
}

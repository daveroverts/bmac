<?php

namespace App\Http\Controllers\EventLink;

use App\Models\Event;
use App\Models\EventLink;
use App\Models\AirportLinkType;
use App\Policies\EventLinkPolicy;
use App\Http\Controllers\AdminController;
use App\Http\Requests\EventLink\Admin\StoreEventLink;
use App\Http\Requests\EventLink\Admin\UpdateEventLink;

class EventLinkAdminController extends AdminController
{
    /**
     * Instantiate a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->authorizeResource(EventLinkPolicy::class, 'eventLink');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $eventLinks = EventLink::orderBy('event_id', 'asc')
            ->with(['event', 'type'])
            ->paginate();
        return view('eventLink.admin.overview', compact('eventLinks'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Event $event)
    {
        $eventLink = new EventLink();
        $eventLinkTypes = AirportLinkType::all();
        $events = Event::where('endEvent', '>', now())
            ->orderBy('startEvent')
            ->get();
        return view('eventLink.admin.form', compact('eventLink', 'eventLinkTypes', 'events'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  StoreEventLink  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreEventLink $request)
    {
        $eventLink = EventLink::create($request->validated());
        flashMessage(
            'success',
            'Done',
            $eventLink->type->name . ' item has been added for ' . $eventLink->event->name
        );
        return redirect(route('admin.eventLinks.index'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\EventLink  $eventLink
     * @return \Illuminate\Http\Response
     */
    public function edit(EventLink $eventLink)
    {
        $eventLinkTypes = AirportLinkType::all();
        return view('eventLink.admin.form', compact('eventLink', 'eventLinkTypes'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  UpdateAirportLink  $request
     * @param  \App\Models\EventLink  $eventLink
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateEventLink $request, EventLink $eventLink)
    {
        $eventLink->update($request->validated());
        flashMessage('success', 'Done', 'Link has been updated');
        return redirect(route('admin.eventLinks.index'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\EventLink  $eventLink
     * @return \Illuminate\Http\Response
     */
    public function destroy(EventLink $eventLink)
    {
        $eventLink->delete();
        flashMessage('success', 'Event link deleted', 'Event link has been deleted');
        return back();
    }
}

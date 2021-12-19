<?php

use App\Models\Event;
use Illuminate\Support\Collection;

function flashMessage($type, $title, $text): void
{
    session()->flash('type', $type);
    session()->flash('title', $title);
    session()->flash('text', $text);
}

function nextEvent($homepage = false): Event
{
    return nextEvents(true, false, $homepage);
}

function nextEvents($one = false, $showAll = false, $homepage = false, $withRelations = []): Event|Collection
{
    $events = Event::where('endEvent', '>', now())
        ->orderBy('startEvent');
    if (! $showAll) {
        $events = $events->where('is_online', true);
    }
    if ($homepage) {
        $events = $events->where('show_on_homepage', true);
    }

    if (! empty($withRelations)) {
        $events = $events->with($withRelations);
    }

    if ($one) {
        $events = $events->first();
    } else {
        $events = $events->get();
    }

    return $events;
}

function nextEventsForFaq(): Collection
{
    return nextEvents(false, false, false, 'faqs');
}

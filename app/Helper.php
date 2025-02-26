<?php

use App\Models\Event;
use Illuminate\Support\Collection;

if (!function_exists('flashMessage')) {
    function flashMessage($type, $title, $text): void
    {
        session()->flash('type', $type);
        session()->flash('title', $title);
        session()->flash('text', $text);
    }
}

if (!function_exists('nextEvent')) {
    function nextEvent($homepage = false): Event
    {
        return nextEvents(true, false, $homepage);
    }
}

if (!function_exists('nextEvents')) {
    function nextEvents($one = false, $showAll = false, $homepage = false, $withRelations = []): Event|Collection
    {
        $events = Event::where('endEvent', '>', now())
            ->orderBy('startEvent');
        if (!$showAll) {
            $events = $events->where('is_online', true);
        }

        if ($homepage) {
            $events = $events->where('show_on_homepage', true);
        }

        if (!empty($withRelations)) {
            $events = $events->with($withRelations);
        }

        return $one ? $events->first() : $events->get();
    }
}

if (!function_exists('nextEventsForFaq')) {
    function nextEventsForFaq(): Collection
    {
        return nextEvents(false, false, false, 'faqs');
    }
}

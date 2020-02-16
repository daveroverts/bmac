<?php

use App\Models\Event;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Redirector;

/**
 * Flash a message that can be used in layouts.alert
 *
 * @param string $type
 * @param string $title
 * @param string $text
 */
function flashMessage($type, $title, $text)
{
    session()->flash('type', $type);
    session()->flash('title', $title);
    session()->flash('text', $text);
}

/**
 * Alias for nextEvent(), limit to 1
 *
 * @param bool $homepage
 *
 * @return Event
 */
function nextEvent($homepage = false)
{
    return nextEvents(true, false, $homepage);
}

/**
 * Function to get upcoming event(s)
 *
 * @param bool $one
 * @param bool $showAll
 * @param bool $homepage
 * @param string|array $withRelations
 *
 * @return Event|Model|null|object
 */
function nextEvents($one = false, $showAll = false, $homepage = false, $withRelations = [])
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

    if ($one) {
        $events = $events->first();
    } else {
        $events = $events->get();
    }

    return $events;
}

/**
 * @return Event|Model|object|null
 */
function nextEventsForFaq()
{
    return nextEvents(false, false, false, 'faqs');
}

/**
 * @return RedirectResponse|Redirector
 */
function holdOnWeGotABadAss()
{
    auth()->logout();
    flashMessage('error', 'Error', 'Something went wrong');
    return redirect('/');
}

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
 * @return Event
 */
function nextEvent()
{
    return nextEvents(true, false);
}

/**
 * Function to get upcoming event(s)
 *
 * @param bool $one
 * @param bool $showAll
 *
 * @return Event|Model|null|object
 */
function nextEvents($one = false, $showAll = false)
{
    $events = Event::upcoming();
    if (!$showAll) {
        $events = $events->where('is_online', true);
    }
    if ($one) {
        $events = $events->first();
    } else {
        $events = $events->get();
    }
    return $events;
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

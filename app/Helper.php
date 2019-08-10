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
    Session::flash('type', $type);
    Session::flash('title', $title);
    Session::flash('text', $text);
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
 * @param bool $one
 * @param bool $showAll
 * @param bool $homepage
 * @return Event|Model|null|object
 */
function nextEvents($one = false, $showAll = false, $homepage = false)
{
    $events = Event::where('endEvent', '>', now())
        ->orderBy('startEvent');
    if (!$showAll) {
        $events = $events->where('is_online', true);
    }
    if ($homepage) {
        $events = $events->where('show_on_homepage', true);
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
    Auth::logout();
    flashMessage('error', 'Error', 'Something went wrong');
    return redirect('/');
}

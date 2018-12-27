<?php

use App\Models\Event;

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
 * @return Event
 */
function nextEvent()
{
    return Event::where('endEvent', '>', now())->orderBy('startEvent', 'asc')->first();
}

/**
 * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
 */
function holdOnWeGotABadAss()
{
    Auth::logout();
    flashMessage('error', 'Error', 'Something went wrong');
    return redirect('/');
}

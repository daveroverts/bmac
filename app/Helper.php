<?php

/**
 * Flash a message that can be used in layouts.alert
 *
 * @param string $type
 * @param string $title
 * @param string $message
 */
function flashMessage($type, $title, $message)
{
    Session::flash('type', $type);
    Session::flash('title', $title);
    Session::flash('message', $message);
}

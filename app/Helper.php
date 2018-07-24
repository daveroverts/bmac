<?php

/**
 * Flash a message that can be used in layouts.alert
 *
 * @param $type
 * @param $title
 * @param $message
 */
function flashMessage($type, $title, $message){
    Session::flash('type', $type);
    Session::flash('title', $title);
    Session::flash('message', $message);
}

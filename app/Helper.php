<?php

if (!function_exists('flashMessage')) {
    function flashMessage($type, $title, $text): void
    {
        session()->flash('type', $type);
        session()->flash('title', $title);
        session()->flash('text', $text);
    }
}

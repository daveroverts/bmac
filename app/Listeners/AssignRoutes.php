<?php

namespace App\Listeners;

use App\Events\FileCreated;
use App\Imports\FlightRouteAssign;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class AssignRoutes implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  \App\Events\FileCreated  $event
     * @return void
     */
    public function handle(FileCreated $event)
    {
        if ($event->file->type != FlightRouteAssign::class) {
            return;
        }

        (new FlightRouteAssign($event->file->fileable))
            ->queue($event->file->path, $event->file->disk);
    }
}

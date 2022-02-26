<?php

namespace App\Listeners;

use App\Events\FileCreated;
use App\Imports\BookingsImport;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Storage;

class ImportBookings implements ShouldQueue
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
     * @param  App\Events\FileCreated  $event
     * @return void
     */
    public function handle(FileCreated $event)
    {
        if ($event->file->type != BookingsImport::class) {
            return;
        }

        (new BookingsImport($event->file->fileable))
            ->import($event->file->path)
            ->chain([
                function () use ($event) {
                    Storage::disk(config('filament.default_filesystem_disk'))
                        ->delete($event->file->path);
                    $event->file->delete();
                }
            ]);
    }
}

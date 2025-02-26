<?php

namespace App\Console\Commands;

use App\Jobs\EventCleanupReservationsJob;
use App\Models\Event;
use Illuminate\Console\Command;

class EventCleanupReservationsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'event:cleanup-reservations {eventId?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up reservations that have exceeded 10 minutes. If no event is provided, it is done for all active events.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $eventId = $this->argument('eventId');
        if ($eventId) {
            $event = Event::find($eventId);
            if (!$event) {
                $this->error("Could not find event with id {$eventId}");
                return Command::FAILURE;
            }
            EventCleanupReservationsJob::dispatch($event);
        } else {
            $this->withProgressBar(nextEvents(), function ($event): void {
                EventCleanupReservationsJob::dispatch($event);
            });
        }
        return Command::SUCCESS;
    }
}

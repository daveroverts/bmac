<?php

use Tests\TestCase;
use App\Jobs\ImportAirportsJob;
use Illuminate\Support\Facades\Queue;
use App\Console\Commands\ImportAirportsCommand;

it('can start airports import', function () {
    /** @var TestCase $this  */
    Queue::fake();

    Queue::assertNothingPushed();
    $this->artisan(ImportAirportsCommand::class)->assertSuccessful();
    Queue::assertPushed(ImportAirportsJob::class);
});

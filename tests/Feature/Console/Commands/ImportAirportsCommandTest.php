<?php

namespace Tests\Feature\Console\Commands;

use App\Console\Commands\ImportAirportsCommand;
use App\Jobs\ImportAirportsJob;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class ImportAirportsCommandTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_command()
    {
        Queue::fake();

        Queue::assertNothingPushed();

        $this->artisan(ImportAirportsCommand::class)->assertSuccessful();
        Queue::assertPushed(ImportAirportsJob::class);
    }
}

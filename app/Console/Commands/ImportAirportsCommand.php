<?php

namespace App\Console\Commands;

use App\Jobs\ImportAirportsJob;
use Illuminate\Console\Command;

class ImportAirportsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:airports';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import airports using the following file: https://raw.githubusercontent.com/mborsetti/airportsdata/main/airportsdata/airports.csv';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        dispatch(new \App\Jobs\ImportAirportsJob());

        return Command::SUCCESS;
    }
}

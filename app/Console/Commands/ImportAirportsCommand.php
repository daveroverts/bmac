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
        ImportAirportsJob::dispatch();

        return Command::SUCCESS;
    }
}

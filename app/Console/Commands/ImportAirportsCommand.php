<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;

#[\Illuminate\Console\Attributes\Description('Import airports using the following file: https://raw.githubusercontent.com/mborsetti/airportsdata/main/airportsdata/airports.csv')]
#[\Illuminate\Console\Attributes\Signature('import:airports')]
class ImportAirportsCommand extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        dispatch(new \App\Jobs\ImportAirportsJob());

        return Command::SUCCESS;
    }
}

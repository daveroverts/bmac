<?php

namespace App\Jobs;

use App\Imports\AirportsImport;
use App\Services\AirportImporter;
use Illuminate\Support\Facades\Storage;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Contracts\Queue\ShouldBeUnique;

class ImportAirportsJob implements ShouldQueue, ShouldBeUnique
{
    use \Illuminate\Foundation\Queue\Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $file = 'import_' . time() . '.csv';
        Storage::disk('local')->put(
            $file,
            file_get_contents(AirportImporter::SOURCE_URL)
        );

        (new AirportsImport())->import($file, 'local');

        Storage::disk('local')->delete($file);
    }
}

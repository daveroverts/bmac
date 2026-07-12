<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use App\Imports\AirportsImport;
use App\Services\AirportImporter;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Queue\ShouldBeUnique;

class ImportAirportsJob implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

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

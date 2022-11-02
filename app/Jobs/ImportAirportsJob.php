<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use App\Imports\AirportsImport;
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
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $file = 'import_' . time() . '.csv';
        Storage::disk('local')->put(
            $file,
            file_get_contents('https://raw.githubusercontent.com/mborsetti/airportsdata/main/airportsdata/airports.csv')
        );
        (new AirportsImport())->queue($file)->chain([
            function () use ($file) {
                Storage::delete($file);
            }
        ]);
    }
}

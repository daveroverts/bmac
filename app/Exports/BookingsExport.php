<?php

namespace App\Exports;

use Illuminate\Contracts\Support\Responsable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\Exportable;


class BookingsExport implements FromQuery, Responsable
{

    use Exportable;

    /**
     * It's required to define the fileName within
     * the export class when making use of Responsable.
     */
    private $fileName = 'bookings.csv';

    public function __construct(int $id)
    {
        $this->id = $id;
    }

    public function query()
    {
        return Booking::query()
            ->where('event_id',$this->id)
            ->whereNotNull('bookedBy_id');
    }
}
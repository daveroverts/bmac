<?php

namespace App\Exports;

use Illuminate\Contracts\Support\Responsable;
use App\Booking;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\Exportable;


class BookingsExport implements FromCollection, Responsable
{

    use Exportable;

    /**
     * It's required to define the fileName within
     * the export class when making use of Responsable.
     */
    private $fileName = 'bookings.csv';

    public function collection()
    {
        return Booking::all();
    }
}
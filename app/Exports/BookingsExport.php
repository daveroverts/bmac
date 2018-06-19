<?php

namespace App\Exports;

use App\Booking;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\Exportable;


class BookingsExport implements FromCollection
{

    use Exportable;

    public function collection()
    {
        return Booking::all();
    }
}
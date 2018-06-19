<?php

namespace App\Exports;

use App\Booking;
use Maatwebsite\Excel\Concerns\FromCollection;


class BookingsExport implements FromCollection
{
    public function collection()
    {
        return Booking::all();
    }
}
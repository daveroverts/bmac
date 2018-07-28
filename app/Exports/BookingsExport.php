<?php

namespace App\Exports;

use Illuminate\Contracts\Support\Responsable;
use App\Models\Booking;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\Exportable;


class BookingsExport implements FromView, Responsable
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

    public function view(): View
    {
        return view('exports.bookings', [
            'bookings' => Booking::where('event_id',$this->id)
            ->whereNotNull('bookedBy_id')
            ->get()
        ]);
    }

}
<?php

namespace App\Http\Requests\Booking;

use App\Http\Requests\Request;
use Illuminate\Support\Facades\Auth;

class UpdateBooking extends Request
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'callsign' => 'sometimes|alpha_num|between:4,7|unique:bookings,callsign,' . Auth::id() . ',user_id,event_id,' . $this->route('booking')->event->id,
            'aircraft' => 'sometimes|alpha_num|between:3,4',
            'selcal1' => 'sometimes|nullable|alpha|size:2',
            'selcal2' => 'sometimes|nullable|required_with:selcal1,!=' . null . '|alpha|size:2',
            'checkStudy' => 'sometimes|accepted',
            'checkCharts' => 'sometimes|accepted',
        ];
    }
}

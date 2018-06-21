<?php

namespace App\Http\Requests;

use App\Booking;
use Illuminate\Foundation\Http\FormRequest;

class UpdateBooking extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $booking = Booking::find($this->route('booking'));
        return [
            'callsign' => 'required|alpha_num|max:7|unique:bookings,callsign,null,null,event_id,'.$booking->event->id,
            'aircraft' => 'required|alpha_num|between:3,4',
            'selcal1' => 'sometimes|nullable|alpha|size:2',
            'selcal2' => 'sometimes|nullable|required_with:selcal1,!='.null.'|alpha|size:2',
            'checkStudy' => 'accepted',
            'checkCharts' => 'accepted',
        ];
    }
}

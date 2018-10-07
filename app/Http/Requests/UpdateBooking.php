<?php

namespace App\Http\Requests;

use Auth;
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
        return [
            'callsign' => 'sometimes|alpha_num|between:4,7|unique:bookings,callsign,'.Auth::id().',user_id,event_id,' . $this->route('booking')->event->id,
            'aircraft' => 'sometimes|alpha_num|between:3,4',
            'selcal1' => 'sometimes|nullable|alpha|size:2',
            'selcal2' => 'sometimes|nullable|required_with:selcal1,!=' . null . '|alpha|size:2',
            'checkStudy' => 'sometimes|accepted',
            'checkCharts' => 'sometimes|accepted',
        ];
    }
}

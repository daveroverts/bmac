<?php

namespace App\Http\Requests\Booking;

use App\Http\Requests\Request;

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
            'callsign' => 'sometimes|alpha_num|between:4,7|unique:bookings,callsign,' . auth()->id() . ',user_id,event_id,' . $this->route('booking')->event->id,
            'acType' => 'sometimes|alpha_num|between:3,4',
            'selcal1' => 'sometimes|nullable|alpha|size:2',
            'selcal2' => 'sometimes|nullable|required_with:selcal1,!=' . null . '|alpha|size:2',
            'checkStudy' => 'sometimes|accepted',
            'checkCharts' => 'sometimes|accepted',
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'checkStudy.accepted' => __('You must agree to study the provided briefing material'),
            'checkCharts.accepted' => __('You must agree to have applicable charts at hand during the event'),
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array
     */
    public function attributes()
    {
        return [
            'callsign' => __('Callsign'),
            'acType' => __('Aircraft code'),
            'selcal1' => __('SELCAL'),
            'selcal2' => __('SELCAL'),
            'checkStudy' => __('Briefing material'),
            'checkCharts' => __('Charts'),
        ];
    }
}

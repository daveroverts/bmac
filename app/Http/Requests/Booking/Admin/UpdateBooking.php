<?php

namespace App\Http\Requests\Booking\Admin;

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
            'is_editable' => 'required|boolean',
            'callsign' => 'nullable|alpha_num|max:7',
            'ctot' => 'present|nullable|date_format:H:i',
            'eta' => 'present|nullable|date_format:H:i',
            'dep' => 'exists:airports,id|different:arr|required',
            'arr' => 'exists:airports,id|required',
            'route' => 'nullable',
            'oceanicFL' => 'nullable|int:3',
            'oceanicTrack' => 'nullable|alpha|min:1|max:2',
            'aircraft' => 'nullable|alpha_num|between:3,4',
            'message' => 'nullable',
        ];
    }
}

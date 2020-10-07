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
            'dep' => 'nullable|exists:airports,id',
            'arr' => 'nullable|exists:airports,id',
            'route' => 'nullable',
            'oceanicFL' => 'nullable|int:3',
            'oceanicTrack' => 'nullable|alpha|min:1|max:2',
            'aircraft' => 'nullable|alpha_num|between:3,4',
            'notes' => 'nullable',
            'message' => 'nullable',
            'notify_user' => 'nullable'
        ];
    }
}

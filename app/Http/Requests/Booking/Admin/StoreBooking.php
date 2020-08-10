<?php

namespace App\Http\Requests\Booking\Admin;

use App\Http\Requests\Request;

class StoreBooking extends Request
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'id' => 'exists:events,id|required',
            'bulk' => 'required|boolean',
            'is_editable' => 'required|boolean',
            'callsign' => 'sometimes|alpha_num|between:4,7',
            'aircraft' => 'sometimes|alpha_num|between:3,4',
            'ctot' => 'sometimes|nullable',
            'eta' => 'sometimes|nullable',
            'route' => 'sometimes|nullable',
            'dep' => 'exists:airports,id|different:arr|required',
            'arr' => 'exists:airports,id|required',
            'start' => 'sometimes|date_format:H:i',
            'end' => 'sometimes|date_format:H:i',
            'separation' => 'sometimes|numeric|min:1',
            'oceanicFL' => 'sometimes|nullable|integer:3',
            'notes' => 'nullable',
        ];
    }
}

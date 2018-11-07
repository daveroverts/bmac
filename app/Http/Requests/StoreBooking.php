<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBooking extends FormRequest
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
            'id' => 'exists:events,id|required',
            'bulk' => 'required|boolean',
            'callsign' => 'sometimes|alpha_num|between:4,7',
            'aircraft' => 'sometimes|alpha_num|between:3,4',
            'ctot' => 'sometimes|nullable',
            'eta' => 'sometimes|nullable',
            'route' => 'sometimes|nullable',
            'from' => 'exists:airports,icao|different:to|required',
            'to' => 'exists:airports,icao|required',
            'start' => 'sometimes|date_format:H:i',
            'end' => 'sometimes|date_format:H:i',
            'separation' => 'sometimes|integer|min:1',
            'oceanicFL' => 'sometimes|integer',
        ];
    }
}

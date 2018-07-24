<?php

namespace App\Http\Requests;

use Illuminate\{
    Foundation\Http\FormRequest, Support\Facades\Auth
};

class AdminUpdateBooking extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        if (Auth::check() && Auth::user()->isAdmin) {
            return true;
        } else return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'callsign' => 'nullable|alpha_num|max:7',
            'ctot' => 'date_format:H:i',
            'ADEP' => 'exists:airports,icao|different:ADES|required',
            'ADES' => 'exists:airports,icao|required',
            'route' => 'nullable',
            'oceanicFL' => 'nullable|int:3',
            'oceanicTrack' => 'nullable|alpha|min:1|max:2',
            'aircraft' => 'nullable|alpha_num|between:3,4',
            'message' => 'nullable',
        ];
    }
}

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
            'callsign' => 'required|alpha_num|max:7',
            'ctot' => 'date_format:H:i',
            'route' => 'nullable|alpha_num',
            'oceanicFL' => 'nullable|int:3',
            'oceanicTrack' => 'nullable|alpha|min:1|max:2',
            'aircraft' => 'required|alpha_num|between:3,4',
        ];
    }
}

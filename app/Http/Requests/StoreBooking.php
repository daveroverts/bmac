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
            'from' => 'exists:airports,icao|different:to|required',
            'to' => 'exists:airports,icao|required',
            'start' => 'date_format:H:i',
            'end' => 'date_format:H:i',
            'separation' => 'integer|min:1',
        ];
    }
}

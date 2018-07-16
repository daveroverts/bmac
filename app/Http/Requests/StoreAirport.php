<?php

namespace App\Http\Requests;

use Illuminate\{
    Foundation\Http\FormRequest, Support\Facades\Auth
};

class StoreAirport extends FormRequest
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
            'icao' => 'required:string|between:4,4',
            'iata' => 'required:string|between:3,3',
            'name' => 'required:string',
        ];
    }
}

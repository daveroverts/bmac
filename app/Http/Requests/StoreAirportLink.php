<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAirportLink extends FormRequest
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
            'airportLinkType_id' => 'exists:airport_link_types,id',
            'airport_id' => 'exists:airports,id',
            'name' => 'nullable|string',
            'url' => 'required|url',
        ];
    }
}

<?php

namespace App\Http\Requests\AirportLink\Admin;

use App\Http\Requests\Request;

class StoreAirportLink extends Request
{
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

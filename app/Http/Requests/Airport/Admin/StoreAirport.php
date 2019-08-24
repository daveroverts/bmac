<?php

namespace App\Http\Requests\Airport\Admin;

use App\Http\Requests\Request;

class StoreAirport extends Request
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'icao' => 'required:string|unique:airports|between:4,4',
            'iata' => 'required:string|unique:airports,iata|between:3,3',
            'name' => 'required:string',
        ];
    }
}

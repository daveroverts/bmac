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
            'icao' => 'required:string|unique:airports|size:4',
            'iata' => 'required:string|unique:airports,iata|size:3',
            'name' => 'required:string',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array
     */
    public function attributes()
    {
        return [
            'icao' => __('ICAO'),
            'iata' => __('IATA'),
            'name' => __('Name'),
        ];
    }
}

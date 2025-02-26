<?php

namespace App\Http\Requests\Airport\Admin;

use App\Http\Requests\Request;

class UpdateAirport extends Request
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'icao' => 'required:string|between:4,4',
            'iata' => 'required:string|between:3,3',
            'name' => 'required:string',
            'latitude' => ['required', 'regex:/^[-]?((([0-8]?[0-9])(\.(\d{1,10}))?)|(90(\.0+)?))$/'],
            'longitude' => ['required', 'regex:/^[-]?((((1[0-7][0-9])|([0-9]?[0-9]))(\.(\d{1,10}))?)|180(\.0+)?)/'],
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
            'latitude' => __('Latitude'),
            'longitude' => __('Longitude'),
        ];
    }
}

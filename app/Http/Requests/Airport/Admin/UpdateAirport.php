<?php

namespace App\Http\Requests\Airport\Admin;

use App\Http\Requests\Request;
use Illuminate\Validation\Rule;

class UpdateAirport extends Request
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'icao' => ['required', 'string', 'size:4', Rule::unique('airports')->ignore($this->route('airport'))],
            'iata' => ['required', 'string', 'size:3', Rule::unique('airports', 'iata')->ignore($this->route('airport'))],
            'name' => ['required', 'string'],
            'latitude' => ['required', 'regex:/^[-]?((([0-8]?[0-9])(\.(\d{1,10}))?)|(90(\.0+)?))$/'],
            'longitude' => ['required', 'regex:/^[-]?((((1[0-7][0-9])|([0-9]?[0-9]))(\.(\d{1,10}))?)|180(\.0+)?)/'],
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array
     */
    #[\Override]
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

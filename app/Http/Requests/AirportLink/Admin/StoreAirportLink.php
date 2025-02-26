<?php

namespace App\Http\Requests\AirportLink\Admin;

use App\Http\Requests\Request;

class StoreAirportLink extends Request
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'airportLinkType_id' => 'required|exists:airport_link_types,id',
            'airport_id' => 'required|exists:airports,id',
            'name' => 'nullable|string',
            'url' => 'required|url',
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
            'airportLinkType_id' => __('Type'),
            'airport_id' => __('Airport'),
            'name' => __('Name'),
            'url' => __('URL'),
        ];
    }
}

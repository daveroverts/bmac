<?php

namespace App\Http\Requests\Airport\Admin;

use App\Http\Requests\Request;

class UpdateAirport extends Request
{
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

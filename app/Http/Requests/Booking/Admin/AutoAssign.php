<?php

namespace App\Http\Requests\Booking\Admin;

use App\Http\Requests\Request;

class AutoAssign extends Request
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'oceanicTrack1' => 'required|alpha|min:1|max:2',
            'oceanicTrack2' => 'required|alpha|min:1|max:2',
            'route1' => 'required',
            'route2' => 'required',
            'minFL' => 'required|int:3',
            'maxFL' => 'required|int:3',
            'checkAssignAllFlights' => 'sometimes',
        ];
    }
}
